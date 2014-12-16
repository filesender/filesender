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
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        
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
     * @param string $mode upload mode ("chunk" or "whole")
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post($id = null, $mode = null) {
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        if(!in_array($mode, array('whole'))) throw new RestBadParameterException('mode');
        
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) {
            $security = 'auth';
        }else if($security != 'key') {
            throw new RestAuthenticationRequiredException();
        }
        
        $file = File::fromId($id);
        
        if($security == 'key') {
            if(!array_key_exists('key', $_GET) || !$_GET['key'] || ($_GET['key'] != $file->uid))
                throw new RestAuthenticationRequiredException();
        }else{
            $user = Auth::user();
            
            if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        }
        
        $data = $this->request->input;
        
        if($mode == 'whole') {
            // Process uploaded file, split into chunks and push to storage
            if(!array_key_exists('file', $_FILES)) throw new RestBadParameterException('file');
            
            $input = $_FILES['file'];
            
            // Check size
            if((int)$input['size'] != $file->size)
                throw new RestException('file_size_does_not_match', 400);
            
            // Check if all files from transfer are done, send notifications if so
            $chunk_size = Config::get('upload_chunk_size');
            if($fh = fopen($input['tmp_name'], 'rb')) {
                for($offset=0; $offset<=$file->size; $offset+=$chunk_size) {
                    $data = fread($fh, $chunk_size);
                    $file->writeChunk($data, $offset);
                }
                
                fclose($fh);
            } else throw new RestException('cannot_open_input_file', 500);
            
            unlink($input['tmp_name']);
        }
        
        return array(
            'path' => '/file/'.$file->id,
            'data' => RestEndpointFile::cast($file)
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
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        if(!in_array($mode, array(null, 'chunk'))) throw new RestBadParameterException('mode');
        if($offset && !is_numeric($offset)) throw new RestBadParameterException('offset');
        if(!$offset) $offset = 0;
        
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) {
            $security = 'auth';
        }else if($security != 'key') {
            throw new RestAuthenticationRequiredException();
        }
        
        $file = File::fromId($id);
        
        if($security == 'key') {
            if(!array_key_exists('key', $_GET) || !$_GET['key'] || ($_GET['key'] != $file->uid))
                throw new RestAuthenticationRequiredException();
        }else{
            $user = Auth::user();
            
            if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        }
        
        $data = $this->request->input;
        if($mode == 'chunk') {
            $client = array();
            foreach(array('X-Filesender-File-Size', 'X-Filesender-Chunk-Offset', 'X-Filesender-Chunk-Size') as $h) {
                $k = 'HTTP_'.strtoupper(str_replace('-', '_', $h));
                $client[$h] = array_key_exists($k, $_SERVER) ? (int)$_SERVER[$k] : null;
            }
            
            if(!is_null($client['X-Filesender-File-Size']))
                if($file->size != $client['X-Filesender-File-Size'])
                    throw new RestSanityCheckFailedException('file_size', $file->size, $client['X-Filesender-File-Size']);
            
            if(!is_null($client['X-Filesender-Chunk-Offset']))
                if($offset != $client['X-Filesender-Chunk-Offset'])
                    throw new RestSanityCheckFailedException('chunk_offset', $offset, $client['X-Filesender-Chunk-Offset']);
            
            if(!is_null($client['X-Filesender-Chunk-Size']))
                if(strlen($data) != $client['X-Filesender-Chunk-Size'])
                    throw new RestSanityCheckFailedException('chunk_size', strlen($data), $client['X-Filesender-Chunk-Size']);
            
            $upload_chunk_size = Config::get('upload_chunk_size');
            $terasender_chunk_size = Config::get('terasender_chunk_size');
            $max_chunk_size = $terasender_chunk_size ? max($upload_chunk_size, $terasender_chunk_size) : $upload_chunk_size;
            if(strlen($data) > $max_chunk_size)
                throw new RestSanityCheckFailedException('chunk_size', strlen($data), 'max '.Config::get('upload_chunk_size'));
            
            if($offset + strlen($data) > $file->size)
                throw new FileChunkOutOfBoundsException($offset, strlen($data), $file->size);
            
            $write_info = $file->writeChunk($data, $offset);
            $file->transfer->isUploading();
            
            return $write_info;
        
        }else if(is_null($mode) && $data->complete) { // Client signals this was the last chunk
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
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        if(!$id) throw new RestMissingParameterException('file_id');
        if(!is_numeric($id)) throw new RestBadParameterException('file_id');
        
        $user = Auth::user();
        $file = File::fromId($id);
        
        if(!$file->transfer->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'file = '.$file->id);
        
        if(count($file->transfer->files) > 1) {
            $file->transfer->removeFile($file);
            
            if($file->transfer->status == 'available') { // Notify deletion for transfers that are available
                $ctn = Lang::translateEmail('file_deleted')->r($file, $file->transfer);
                foreach($file->transfer->recipients as $recipient) {
                    $mail = new ApplicationMail($ctn->r($recipient));
                    $mail->to($recipient);
                    $mail->send();
                }
            }
        } else { // Last/only file deletion => close transfer
            $file->transfer->close();
        }
    }
}
