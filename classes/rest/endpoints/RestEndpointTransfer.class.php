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
 * REST transfer endpoint
 */
class RestEndpointTransfer extends RestEndpoint {
    /**
     * Cast a Transfer to an array for response
     * 
     * @param Transfer $transfer
     * @param array $files_cids files client given id for strict matching
     * 
     * @return array
     */
    public static function cast(Transfer $transfer, $files_cids = null) {
        return array(
            'id' => $transfer->id,
            'user_id' => $transfer->user_id,
            'user_email' => $transfer->user_email,
            'subject' => $transfer->subject,
            'message' => $transfer->message,
            'created' => RestUtilities::formatDate($transfer->created),
            'expires' => RestUtilities::formatDate($transfer->expires),
            'options' => $transfer->options,
            
            'files' => array_map(function($file) use($files_cids) {
                $file = RestEndpointFile::cast($file);
                if($files_cids && array_key_exists($file['id'], $files_cids))
                    $file['cid'] = $files_cids[$file['id']];
                return $file;
            }, array_values($transfer->files)),
            'recipients' => array_map('RestEndpointRecipient::cast', array_values($transfer->recipients)),
        );
    }
    
    /**
     * Get info about a transfer
     * 
     * Call examples :
     *  /transfer : list of user available transfers (same as /transfer/@me)
     *  /transfer/@all : list of all available transfers (admin only)
     *  /transfer/17 : info about transfer with id 17
     *  /transfer/17/file : files in transfer with id 17
     *  /transfer/17/file/42 : info about file with id 42 in transfer with id 17
     *  /transfer/17/recipient : recipients in transfer with id 17
     *  /transfer/17/recipient/11 : info about recipient with id 11 in transfer with id 17
     * 
     * @param int $id transfer id to get info about
     * @param string $property sub-property to get info about ("file" or "recipient")
     * @param int $property_id id of sub-property entry to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function get($id = null, $property = null, $property_id = null) {
        if($property == 'options' && in_array($property_id, array('enable_recipient_email_download_complete'))) {
            if(!array_key_exists('token', $_GET)) throw new RestBadParameterException('token');
            $token = $_GET['token'];
            if(!Utilities::isValidUID($token)) throw new RestBadParameterException('token');
            
            if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
            
            $transfer = Transfer::fromId($id);
            $recipient = Recipient::fromToken($token);
            
            if(!$recipient->transfer->is($transfer)) throw new RestAuthenticationRequiredException();
            
            return in_array($property_id, $transfer->options);
        }
        
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if(is_numeric($id)) {
            $transfer = Transfer::fromId($id);
            
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
            
            if($property == 'options')
                return $transfer->options;
            
            if($property == 'auditlog') {
                if($property_id == 'mail') {
                    $report = new Report($transfer);
                    $report->sendTo(Auth::user());
                    return true;
                }
                
                return array_values(array_map(function($log) {
                    $author_data = array(
                        'type' => $log->author_type,
                        'id' => $log->author_id,
                        'ip' => $log->ip
                    );
                    if($log->author_type == 'Recipient')
                        $author_data['email'] = $log->author->email;
                    
                    $target = $log->target;
                    $target_data = array(
                        'type' => $log->target_type,
                        'id' => $log->target_id
                    );
                    switch($log->target_type) {
                        case 'Transfer': break;
                        case 'File':
                            $target_data['name'] = $target->name;
                            $target_data['size'] = $target->size;
                            break;
                        case 'Recipient':
                            $target_data['email'] = $target->email;
                            break;
                    }
                    
                    return array(
                        'date' => RestUtilities::formatDate($log->created, true),
                        'event' => $log->event,
                        'author' => $author_data,
                        'target' => $target_data
                    );
                }, $transfer->auditlogs));
            }
            
            return self::cast($transfer);
        }
        
        if(!in_array($id, array('', '@me', '@all'))) throw new RestBadParameterException('transfer_id');
        
        if($id == '@all') {
            if(!Auth::isAdmin()) throw new RestAdminRequiredException();
            
            $transfers = Transfer::all(Transfer::AVAILABLE);
            
        }else{ // $id == @me or empty
            $transfers = Transfer::fromUser($user);
        }
        
        $out = array();
        foreach($transfers as $transfer) $out[] = self::cast($transfer);
        
        return $out;
    }
    
    /**
     * Create new transfer or add recipient to an existing transfer
     * 
     * Call examples :
     *  /transfer : create new transfer from request (including files and recipients)
     *  /transfer/17/recipient : add a recipient to transfer with id 17
     * 
     * @param int $id transfer id to get info about
     * @param string $property sub-property to get info about ("file" or "recipient")
     * @param int $property_id id of sub-property entry to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post($id = null, $add = null) {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if($id) {
            if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
            if($add != 'recipient') throw new RestBadParameterException('add');
        }
        
        if(is_numeric($id)) {
            $transfer = Transfer::fromId($id);
            
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
            
            // Add recipient
            $data = $this->request->input;
            
            $recipient = $transfer->addRecipient($data->recipient);

            // Send email
            
            return array(
                'path' => '/recipient/'.$recipient->id,
                'data' => RestEndpointRecipient::cast($recipient)
            );
        }else{
            // New transfer
            $data = $this->request->input;
            
            if(!count($data->files))
                throw new TransferNoFilesException();
            
            if(!count($data->recipients))
                throw new TransferNoFilesException();
            
            $transfer = Transfer::create($data->expires, $data->from);
            
            if($data->subject) $transfer->subject = $data->subject;
            if($data->message) $transfer->message = $data->message;
            
            $transfer->options = $data->options;
            
            $transfer->save(); // Mandatory to add recipients and files
            
            $banExtensions = Config::get('ban_extension');
            if(!is_null($banExtensions) && !is_array($banExtensions)) $banExtensions = array_map('trim', explode(',', $banExtensions));
            
            $cptFiles = 0;
            // TODO limit file count
            // TODO limit size
            // TODO check size against available space
            $files_cids = array();
            foreach($data->files as $filedata) {
                $ext = pathinfo($filedata->name, PATHINFO_EXTENSION);
                if ($banExtensions !== null){
                    if (!in_array($ext,$banExtensions) ){
                        $file = $transfer->addFile($filedata->name, $filedata->size, $filedata->mime_type);
                        $files_cids[$file->id] = $filedata->cid;
                    }else{
                        throw new FileExtensionNotAllowedException($ext);
                    }
                }
            }
            
            foreach($data->recipients as $email) $transfer->addRecipient($email);
            
            $transfer->start();
            
            return array(
                'path' => '/transfer/'.$transfer->id,
                'data' => self::cast($transfer, $files_cids)
            );
        }
    }
    
    /**
     * Signal upload transfer complete
     * 
     * Call examples :
     *  /transfer/17/complete : signal transfer with id 17 completion
     * 
     * @param int $id transfer id to get info about
     * @param string $complete ("complete")
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null) {
        if(!$id) throw new RestMissingParameterException('transfer_id');
        if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
        
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $transfer = Transfer::fromId($id);
        
        $user = Auth::user();
        
        if(!$transfer->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
        
        $data = $this->request->input;
        
        if($data->complete)
            $transfer->makeAvailable();
        
        if($data->closed)
            $transfer->close(true);
        
        return true;
    }
    
    /**
     * Delete (closes) a transfer
     * 
     * Call examples :
     *  /transfer/17 : close transfer with id 17
     * 
     * @param int $id transfer id to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null) {
        if(!$id) throw new RestMissingParameterException('transfer_id');
        if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
        
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) {
            $security = 'auth';
        }else if($security != 'key') {
            throw new RestAuthenticationRequiredException();
        }
        
        $transfer = Transfer::fromId($id);
        
        if($security == 'key') {
            try {
                if(!array_key_exists('key', $_GET)) throw new Exception();
                if(!$_GET['key']) throw new Exception();
                if(!File::fromUid($_GET['key'])->transfer->is($transfer)) throw new Exception();
            } catch(Exception $e) {
                throw new RestAuthenticationRequiredException();
            }
        }else{
            $user = Auth::user();
            
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
        }
        
        $nice = array_key_exists('nice', $_GET) && (bool)$_GET['nice'];
        
        if($nice) $transfer->close(true);
        
        $transfer->delete();
    }
}
