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
     * @param object $file the file
     * 
     * @return array
     */
    public static function cast($file) {
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
     *  /file/17/chunk : add chunk to the file with id 17
     *  /file/17/whole : upload file as a whole
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
        if(!in_array($mode, array('chunk', 'whole'))) throw new RestBadParameterException('mode');
        
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
            $write_info = $file->writeChunk($data); // No offset => append at end of file
            
            return array(
                'path' => '/file/'.$file->id.'/chunk/'.$write_info['offset'],
                'data' => $write_info
            );
            
        }else if($mode == 'whole') {
            // Process uploaded file, split into chunks and push to storage
            
            // Check hash
            
            // Check if all files from transfer are done, send notifications if so
            
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
        if(!in_array($mode, array('chunk', 'complete'))) throw new RestBadParameterException('mode');
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
        
        if($mode == 'chunk') {
            $data = $this->request->input;
            
            $write_info = $file->writeChunk($data, $offset);
            
            return $write_info;
        
        }else if($mode == 'complete') { // Client signals this was the last chunk
            $data = $this->request->input;
            
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
        
        $transfer = $file->transfer; // Before deletion so that we are sure data is available
        
        $file->delete();
        
        if($transfer->status != 'available') return null; // Do not notify closure for transfers that are not available
        
        // Send emails
        foreach($transfer->recipient as $recipient) {
            // Notify $file deletion
        }
    }
}
