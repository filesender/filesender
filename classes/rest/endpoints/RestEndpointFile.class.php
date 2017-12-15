<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

/**
 * REST file endpoint
 */
class RestEndpointFile extends RestEndpoint {
    /**
     * Cast a File to an array for response
     * 
     * @param File $file
     * 
     * @return array
     */
    public static function cast(File $file) {
        return array(
            'id' => $file->id,
            'transfer_id' => $file->transfer_id,
            'uid' => $file->uid,
            'name' => $file->name,
            'size' => $file->size,
            'sha1' => $file->sha1
        );
    }
    
    /**
     * Check wether security token match is needed
     * 
     * @param
     * 
     * @return bool
     */
    public function requireSecurityTokenMatch($method, $path) {
        $security = Config::get('chunk_upload_security');
        $path = implode('/', $path);
        
        if(Auth::isRemote()) // Remote auth doesn't need token
            return false;
        
        if($security == 'auth') // Need token if auth mode
            return true;
        
        if(!array_key_exists('key', $_GET)) // No key, need token
            return true;
        
        if(!$_GET['key']) // No key, need token
            return true;

        if(($method == 'put') && preg_match('`^[0-9]+$`', $path)) // No need if key and signal upload complete
            return false;

        if(($method == 'post') && preg_match('`^[0-9]+/whole$`', $path)) // No need if key and whole file upload
            return false;
        
        if(($method == 'put') && preg_match('`^[0-9]+/chunk(/.*)?$`', $path)) // No need if key and chunk upload
            return false;
        
        return true; // Need token for every other situation
    }

    /**
     * Get info about a file
     * 
     * Call examples :
     *  /file/17 : info about file with id 17
     * 
     * @param int $id file id to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function get($id = null) {
        // Need to be authenticated
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        // Check parameters
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        
        // Get required file and current user then check ownership
        $user = Auth::user();
        $file = File::fromId($id);
        
        if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        
        return self::cast($file);
    }
    
    /**
     * Add chunk to a file or upload whole file
     * 
     * Call examples :
     *  /file/17/whole : upload file as a whole (legacy mode)
     * 
     * @param int $id transfer id to get info about
     * @param string $mode upload mode ("whole")
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post($id = null, $mode = null) {
        // Check parameters
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        if(!in_array($mode, array('whole'))) throw new RestBadParameterException('mode');
        
        // Evaluate security type depending on config and auth
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) $security = 'auth';
        if(($security == 'auth') && !Auth::isAuthenticated())
            throw new RestAuthenticationRequiredException();
        
        // Get related file object
        $file = File::fromId($id);
        
        // Check access rights depending on config
        if($security == 'key') {
            if(!array_key_exists('key', $_GET) || !$_GET['key'] || ($_GET['key'] != $file->uid))
                throw new RestAuthenticationRequiredException();
        }else{
            $user = Auth::user();
            
            if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        }
        
        // Get chunk data
        $data = $this->request->input;

        // File's Transfer must be uploading or just started, fail otherwise
        if( $file->transfer->status != TransferStatuses::STARTED &&
            $file->transfer->status != TransferStatuses::UPLOADING
        ) {
            throw new RestCannotAddDataToCompleteTransferException('File', $file->id);
        }

        
        if($mode == 'whole') {
            // Process uploaded file, split into chunks and push to storage
            if(!array_key_exists('file', $_FILES)) throw new RestBadParameterException('file');
            
            $input = $_FILES['file'];
            
            // Check size
            if((int)$input['size'] != $file->size)
                throw new RestException('file_size_does_not_match', 400);
            
            // Check that storage backend supports whole file mode
            if(Storage::supportsWholeFile()) {
                // Supported, store in one go
                Storage::storeWholeFile($file, $input['tmp_name']);
                
            } else {
                // Not supported, slice file and store chunk by chunk
                $chunk_size = Config::get('upload_chunk_size');
                if($fh = fopen($input['tmp_name'], 'rb')) {
                    for($offset=0; $offset<=$file->size; $offset+=$chunk_size) {
                        $data = fread($fh, $chunk_size);
                        $file->writeChunk($data, $offset);
                    }
                    
                    fclose($fh);
                } else throw new CoreCannotReadFileException($input['tmp_name']);
            }
            
            // Remove temporary file that was created by PHP
            unlink($input['tmp_name']);
        }
        
        $data = RestEndpointFile::cast($file);
        
        if(array_key_exists('callback', $_REQUEST) && array_key_exists('iframe_callback', $_REQUEST))
            $data['security_token'] = Utilities::getSecurityToken();
        
        return array(
            'path' => '/file/'.$file->id,
            'data' => $data
        );
    }
    
    /**
     * Add chunk to a file at offset
     * 
     * Call examples :
     *  /file/17/chunk/2587 : add chunk to the file with id 17 at offset 2587
     * 
     * @param int $id transfer id to get info about
     * @param string $mode upload mode ("chunk")
     * @param int $offset chunk offset
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null, $mode = null, $offset = null) {
        // Check parameters
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        if(!in_array($mode, array(null, 'chunk'))) throw new RestBadParameterException('mode');
        if($offset && !is_numeric($offset)) throw new RestBadParameterException('offset');
        if(!$offset) $offset = 0;
        
        // Evaluate security type depending on config and auth
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) $security = 'auth';
        if(($security == 'auth') && !Auth::isAuthenticated())
            throw new RestAuthenticationRequiredException();
        
        // Get file we need to add data to or update
        $file = File::fromId($id);
        
        // Check access rights depending on config
        if($security == 'key') {
            if(!array_key_exists('key', $_GET) || !$_GET['key'] || ($_GET['key'] != $file->uid))
                throw new RestAuthenticationRequiredException();
        }else{
            $user = Auth::user();
            
            if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        }
        
        // Get request data
        $data = $this->request->input;
        
        if($mode == 'chunk') {
            // Need to put a chunk of data
            
            // File's Transfer must be uploading or just started, fail otherwise
            if(
                $file->transfer->status != TransferStatuses::STARTED &&
                $file->transfer->status != TransferStatuses::UPLOADING
            ) throw new RestCannotAddDataToCompleteTransferException('File', $file->id);
            
            // Get integrity check data sent from the client
            $client = array();
            foreach (array('X-Filesender-File-Size', 'X-Filesender-Chunk-Offset', 'X-Filesender-Chunk-Size', 'X-Filesender-Encrypted') as $h) {
                $k = 'HTTP_' . strtoupper(str_replace('-', '_', $h));
                $client[$h] = array_key_exists($k, $_SERVER) ? (int) $_SERVER[$k] : null;
            }

            if ($file->transfer->options['encryption']) {
                // get rid of the base64
                $data = base64_decode($data);
                // Calculate the correct length
                $chunkLength = strlen($data);
                // The encryption adds padding and a checksum
                $paddedLength = 16 - $client['X-Filesender-Chunk-Size'] % 16;
                if ($paddedLength == 0) {
                    $paddedLength = 16;
                }
                // The initialization vector
                $ivLength = 16;
                // Content length
                $data_length = ($chunkLength - $paddedLength - $ivLength);
            } else {
                $data_length = strlen($data);
            }
            
            // Check that the client sent file size the same as the loaded file if given
            if(!is_null($client['X-Filesender-File-Size']))
                if($file->size != $client['X-Filesender-File-Size'])
                    throw new RestSanityCheckFailedException('file_size', $file->size,
                                                             $client['X-Filesender-File-Size'],
                                                             $file, $client );
            
            // Check that the offset from check data and the one in the url are the same if given
            if(!is_null($client['X-Filesender-Chunk-Offset']))
                if($offset != $client['X-Filesender-Chunk-Offset'])
                    throw new RestSanityCheckFailedException('chunk_offset', $offset,
                                                             $client['X-Filesender-Chunk-Offset'],
                                                             $file, $client );
            
            // Check that the sent data size is the one givent by the client
            if(!is_null($client['X-Filesender-Chunk-Size']))
                if($data_length != $client['X-Filesender-Chunk-Size'])
                    throw new RestSanityCheckFailedException('chunk_size', $data_length,
                                                             $client['X-Filesender-Chunk-Size'],
                                                             $file, $client );

            // Check that data length does not exceed upload_chunk_size (can be smaller at the end of the file)
            $upload_chunk_size = Config::get('upload_chunk_size');
            $upload_crypted_chunk_size = Config::get('upload_crypted_chunk_size');

            if ($data_length > $upload_chunk_size) {
                if (( $file->transfer->options['encryption'] && $data_length > $upload_crypted_chunk_size )) {
                    throw new RestSanityCheckFailedException('chunk_size', $data_length,
                                                             'max ' . Config::get('upload_chunk_size'),
                                                             $file, $client );
                }
            }

            // Check that chunk offset is inside the file bounds
            if($offset + $data_length > $file->size) {
                throw new FileChunkOutOfBoundsException($file, $offset, $data_length, $file->size);
            }
            
            // Write data to file and calculate the offset with crypted size in mind
            if ($file->transfer->options['encryption']) {
                $offset = $offset / Config::get('upload_chunk_size') * Config::get('upload_crypted_chunk_size');
            }

            $write_info = $file->writeChunk($data, $offset);
            $file->transfer->isUploading();
            
            return $write_info;
        
        }else if(is_null($mode) && $data && $data->complete) {
            // Client signals that the file's body has been fully uploaded, flag the file as complete
            
            $file->complete();
            
            return true;
        }
    }
    
    /**
     * Delete a file
     * 
     * Call examples :
     *  /file/17 : close file with id 17
     * 
     * @param int $id file id to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null) {
        // Need to be authenticated
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        // Check parameters
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        
        // Get file object and user ...
        $user = Auth::user();
        $file = File::fromId($id);
        
        // ... and check ownership
        if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        
        if(count($file->transfer->files) > 1) {
            // Several files in the transfer, remove file
            $file->transfer->removeFile($file);
            
            if($file->transfer->status == 'available') {
                // Notify deletion for transfers that are available
                foreach($file->transfer->recipients as $recipient) {
                    $file->transfer->sendToRecipient('file_deleted', $recipient, $file);
                }
            }
            
        } else {
            // Last/only file deletion => close transfer
            $file->transfer->close();
        }
    }
}
