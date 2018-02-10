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
            'expiry_date_extension' => $transfer->expiry_date_extension,
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
        
        if(($method == 'put') && preg_match('`^[0-9]+$`', $path)) // No need if key and transfer properties set
            return false;
        
        if(($method == 'delete') && preg_match('`^[0-9]+$`', $path)) // No need if key and transfer delete
            return false;
        
        return true; // Need token for every other situation
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
        // Special case when checking if enable_recipient_email_download_complete option is enabled for a specific transfer 
        if($property == 'options' && 'enable_recipient_email_download_complete' == $property_id ) {
            
            // Check that we have a valid token in the url
            if(!array_key_exists('token', $_GET)) throw new RestBadParameterException('token');
            $token = $_GET['token'];
            if(!Utilities::isValidUID($token)) throw new RestBadParameterException('token');
            
            // Check that we have a valid transfer id
            if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
            
            // Get transfer and recipient from above data
            $transfer = Transfer::fromId($id);
            $recipient = Recipient::fromToken($token);
            
            // Check relationship between the two
            if(!$recipient->transfer->is($transfer)) throw new RestAuthenticationRequiredException();
            
            return in_array($property_id, $transfer->options);
        }
        
        // If key was provided we validate it and return the transfer (guest restart)
        if(is_numeric($id) && array_key_exists('key', $_GET) && $_GET['key']) {
            $transfer = Transfer::fromId($id);
            try {
                if(!File::fromUid($_GET['key'])->transfer->is($transfer)) throw new Exception();
                if(!$transfer->isStatusUploading()) throw new Exception();
            } catch(Exception $e) {
                throw new RestAuthenticationRequiredException();
            }
            
            return self::cast($transfer);
            
        } else {
            
            // Need to be authenticated
            if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        }
        
        // Get current user
        $user = Auth::user();
        
        if(is_numeric($id)) {
            // Getting data about a specific transfer
            $transfer = Transfer::fromId($id);
            
            // Check ownership
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
            
            // Only want transfer options
            if($property == 'options')
                return $transfer->options;
            
            // Want auditlog data ...
            if($property == 'auditlog') {
                if($property_id == 'mail') {
                    // ... to be sent by email
                    $report = new Report($transfer);
                    $report->sendTo(Auth::user());
                    return true;
                }
                
                // ... to be returned, aggregate it
                return array_values(array_map(function($log) {
                    $author = $log->author;
                    
                    // Build action author data
                    $author_data = array(
                        'type' => $log->author_type,
                        'id' => $log->author_id,
                        'identity' => $author ? (string)$author->identity : null,
                        'ip' => $log->ip
                    );
                    if($log->author_type == 'Recipient')
                        $author_data['email'] = $log->author->email;
                    
                    // Build action target data
                    $target = $log->target;
                    $target_data = array(
                        'type' => $log->target_type,
                        'id' => $log->target_id
                    );
                    $time_taken = null;
                    
                    // Add additional data depending on target type
                    switch($log->target_type) {
                        case 'Transfer': // Actions on transfer gets time taken added (like time between start and end of upload)
                            if($log->event == LogEventTypes::TRANSFER_AVAILABLE)
                                $time_taken = $target->made_available_time;
                            
                            if($log->event == LogEventTypes::UPLOAD_ENDED)
                                $time_taken = $target->upload_time;
                            break;
                            
                        case 'File': // Actions on file gets file name, file size and upload time added
                            $target_data['name'] = $target->name;
                            $target_data['size'] = $target->size;
                            
                            if($log->event == LogEventTypes::FILE_UPLOADED)
                                $time_taken = $target->upload_time;
                            break;
                            
                        case 'Recipient': // Actions on recipient gets recipient email added
                            $target_data['email'] = $target->email;
                            break;
                    }
                    
                    return array(
                        'date' => RestUtilities::formatDate($log->created, true),
                        'event' => $log->event,
                        'author' => $author_data,
                        'target' => $target_data,
                        'time_taken' => array(
                            'raw' => $time_taken,
                            'formatted' => $time_taken ? Utilities::formatTime($time_taken) : '0s'
                        ),
                    );
                }, $transfer->auditlogs));
            }
            
            // No specific info to get, return the whole transfer
            return self::cast($transfer);
        }
        
        // All transfers request
        
        // Check parameters
        if(!in_array($id, array('', '@me', '@all'))) throw new RestBadParameterException('transfer_id');
        
        if($id == '@all') {
            // Need to be admin to get info about all transfers from all users
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
        // Need to be authenticated
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        // Check parameters
        if($id) {
            if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
            if($add != 'recipient') throw new RestBadParameterException('add');
        }
        
        if(is_numeric($id)) {
            // Add data to a specific transfer
            $transfer = Transfer::fromId($id);
            
            // Check ownership
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
            
            // Cannot update a closed transfer
            if($transfer->status == TransferStatuses::CLOSED)
                throw new RestException('cannot_alter_closed_transfer' ,403);
            
            // Raw data
            $data = $this->request->input;
            
            if($data->recipient) {
                // Add recipient
                $recipient = $transfer->addRecipient($data->recipient);
                
                // Send email if transfer is live already
                if($transfer->status == TransferStatuses::AVAILABLE)
                    TranslatableEmail::quickSend('transfer_available', $recipient, $transfer);
                
                return array(
                    'path' => '/recipient/'.$recipient->id,
                    'data' => RestEndpointRecipient::cast($recipient)
                );
            }
            
        }else{
            // Create new transfer
            
            // Raw data
            $data = $this->request->input;
            
            // Is it created by a guest ?
            $guest = null;
            if(Auth::isGuest()) $guest = AuthGuest::getGuest();
            
            // Must have files ...
            if(!count($data->files))
                throw new TransferNoFilesException();
            
            // ... but not too much
            $maxfiles = Config::get('max_transfer_files');
            if($maxfiles && count($data->files) > $maxfiles)
                throw new TransferTooManyFilesException(count($data->files), $maxfiles);
            
            // Allow any options for remote applications, check against allowed options otherwise
            $allowed_options = array_keys(Auth::isRemoteApplication() ? Transfer::allOptions() : Transfer::availableOptions());
            
            // Build options from provided data and defaults
            $allOptions = Transfer::allOptions();
            $options = array(
                TransferOptions::GET_A_LINK => $allOptions[TransferOptions::GET_A_LINK]['default'],
                TransferOptions::ADD_ME_TO_RECIPIENTS => $allOptions[TransferOptions::ADD_ME_TO_RECIPIENTS]['default'],
            );
            
            foreach($allOptions as $name => $dfn)  {
                if(in_array($name, $allowed_options)) {
                    if (method_exists($data->options,'exists')) {
                        if ($data->options->exists($name))
                            $options[$name] = $data->options->$name;
                    } else {
                        if (array_search($name,$data->options) !== FALSE)
                            $options[$name] = 1;
                    }
                }
            }
            
            $options['encryption'] = $data->encryption;

            Logger::info($options);
            // Get_a_link transfers have no recipients so mail related options make no sense, remove them if set
            if($options[TransferOptions::GET_A_LINK]) {
                unset($options[TransferOptions::EMAIL_ME_COPIES]);
                unset($options[TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE]);
                unset($options[TransferOptions::ADD_ME_TO_RECIPIENTS]);
            }
            
            // No recipients, not get_a_link and no way to get a recipient from options ? Fail if so
            if(
                !count($data->recipients) &&
                !$options[TransferOptions::GET_A_LINK] &&
                !$options[TransferOptions::ADD_ME_TO_RECIPIENTS] &&
                (
                    !$guest ||
                    (
                        !$guest->transfer_options[TransferOptions::ADD_ME_TO_RECIPIENTS] &&
                        !$guest->options[GuestOptions::CAN_ONLY_SEND_TO_ME]
                    )
                )
            ) {
                throw new TransferNoRecipientsException();
            }     
            
            // Check if not too much recipients
            $maxrecipients = Config::get('max_transfer_recipients');
            if($maxrecipients && count($data->recipients) > $maxrecipients)
                throw new TransferTooManyRecipientsException(count($data->recipients), $maxrecipients);
            
            // Compute total size ...
            $size = array_sum(array_map(function($f) {
                return (int)$f->size;
            }, $data->files));
            
            // ... check it against max size ...
            $maxsize = Config::get('max_transfer_size');
            if($maxsize && $size > $maxsize)
                throw new TransferMaximumSizeExceededException($size, $maxsize);

            // ... check that each file is under the per file size limit
            foreach($data->files as $filedata) {
                $sz = $filedata->size;
                $v = 0;
                
                if( !$data->encryption ) {
                    $v = Config::get('max_transfer_file_size');
                    if( $v && $sz > $v ) {
                        throw new TransferMaximumFileSizeExceededException($sz, $v );
                    }
                } else {
                    $v = Config::get('max_transfer_encrypted_file_size');
                    if( $v && $sz > $v ) {
                        throw new TransferMaximumEncryptedFileSizeExceededException($sz, $v );
                    }
                }
                
            }

            // ... check if it exceeds host quota (if enabled) ...
            $host_quota = Config::get('host_quota');
            if($host_quota) {
                $usage = Transfer::getUsage();
                
                if($size > $usage['available'])
                    throw new TransferHostQuotaExceededException();
            }
            
            // ... check if it exceeds user quota (if enabled)
            $user_quota = Config::get('user_quota');
            if($user_quota || ($user_quota === 0)) {
                // If guest use saved owner quota
                if($guest)
                    $user_quota = $guest->owner->quota;

                $remaining = $user_quota - array_sum(array_map(function($t) {
                    return $t->size;
                }, Transfer::fromUser(Auth::user())));
                
                if($size > $remaining)
                    throw new TransferUserQuotaExceededException();
            }
            
            // Every check went well, create the transfer
            $expires = $data->expires ? $data->expires : Transfer::getDefaultExpire();
            $transfer = Transfer::create($expires, $guest ? $guest->email : $data->from);
            
            // Set additional data
            if($data->subject) $transfer->subject = $data->subject;
            if($data->message) {
                $transfer->message = $data->message;
                if(!Utilities::isValidMessage($transfer->message)) {
                    throw new TransferMessageBodyCanNotIncludeURLsException();
                }
            }
            if(Config::get('transfer_recipients_lang_selector_enabled') && $data->lang) $transfer->lang = $data->lang;
            
            // Guest owner decides about guest options
            if($guest) {
		$options = $guest->transfer_options;
	    }
	    $options['encryption'] = $data->encryption;
            Logger::info($options);
            $transfer->options = $options;
            
            $transfer->save(); // Mandatory to add recipients and files
            
            // Get banned extensions
            $banned_exts = Config::get('ban_extension');
            if(is_string($banned_exts)) $banned_exts = array_map('trim', explode(',', $banned_exts));
            $extension_whitelist_regex = Config::get('extension_whitelist_regex');
            
            // Add files after checking that they do not have a banned extension, fail otherwise
            $files_cids = array();
            foreach($data->files as $filedata) {
                $ext = pathinfo($filedata->name, PATHINFO_EXTENSION);

		if( $extension_whitelist_regex != ''
                 && preg_match( '/' . $extension_whitelist_regex . '/', $ext ) === 0 ) {
		    throw new FileExtensionNotAllowedException($ext);
		}
		
                if(!is_null($banned_exts) && in_array($ext, $banned_exts))
		    throw new FileExtensionNotAllowedException($ext);
                
                $file = $transfer->addFile($filedata->name, $filedata->size, $filedata->mime_type);
                $files_cids[$file->id] = $filedata->cid;
            }
            
            // Add recipient(s) depending on options
            if($transfer->getOption(TransferOptions::GET_A_LINK)) {
                $transfer->addRecipient(''); // Anonymous recipient = without email
                
            } else if($guest && $guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
                $transfer->addRecipient($guest->user_email);
                
            } else {
                foreach($data->recipients as $email)
                $transfer->addRecipient($email);
                
                $email = $guest ? $guest->user_email : ($data->from ? $data->from : Auth::user()->email);
                if($transfer->getOption(TransferOptions::ADD_ME_TO_RECIPIENTS) && !$transfer->isRecipient($email))
                    $transfer->addRecipient($email);
            }
            
            // Here we have everything (uids ...) to check if the transfer fits in storage
            if(!Storage::canStore($transfer)) {
                $transfer->delete();
                throw new StorageNotEnoughSpaceLeftException($transfer->size);
            }
            
            // Run transfer creation validators if defined in config, delete newly created transfers if any fails
            $validators = Config::get('transfer_validators');
            if(is_array($validators)) foreach($validators as $validator) {
                if(!is_callable($validator)) continue;
                
                try {
                    $ok = call_user_func($validator, $transfer);
                    if(is_bool($ok) && !$ok) throw new Exception('no reason given');
                } catch(Exception $e) { // Catch any, delete and re-throw as typed exception
                    $transfer->delete();
                    throw new TransferRejectedException($e->getMessage());
                }
            }
            
            // Tag the transfer as started, that is, ready for file upload
            $transfer->start();
            
            return array(
                'path' => '/transfer/'.$transfer->id,
                'data' => self::cast($transfer, $files_cids)
            );
        }
    }
    
    /**
     * Update a transfer's status
     * 
     * Call examples :
     *  /transfer/17, payload: {complete: true} : signal transfer with id 17 completion
     *  /transfer/17, payload: {closed: true} : close a transfer
     *  /transfer/17, payload: {remind: true} : remind a transfer to recipients
     * 
     * @param int $id transfer id to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null) {
        // Check parameters
        if(!$id) throw new RestMissingParameterException('transfer_id');
        if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
        
        // Evaluate security type depending on config and auth
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) $security = 'auth';
        if(($security == 'auth') && !Auth::isAuthenticated())
            throw new RestAuthenticationRequiredException();
        
        // Get transfer to update and current user
        $transfer = Transfer::fromId($id);
        $user = Auth::user();
        
        // Raw update data
        $data = $this->request->input;
        
        // Check access rights depending on config
        if($security == 'key') {
            try {
                if(!array_key_exists('key', $_GET)) throw new Exception();
                if(!$_GET['key']) throw new Exception();
                if(!File::fromUid($_GET['key'])->transfer->is($transfer)) throw new Exception();
                if($transfer->isStatusClosed()) throw new Exception();
                
                if($data->complete && $transfer->isStatusUploading()) {
                    // this block means we are ok
                    // these are the options that we allow
                } else {
                    // bad attempt
                    throw new Exception();
                }
            } catch(Exception $e) {
                throw new RestAuthenticationRequiredException();
            }
            
        } else {
            // check ownership
            if(!$transfer->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
            
            // Close and remind action need to stay here as only complete action is allowed with either session or key
            
            // Need to close the transfer upon user request ?
            if($data->closed)
                $transfer->close(true);
            
            // Need to extend expiry date
            if($data->extend_expiry_date)
                $transfer->extendExpiryDate();
            
            // Need to remind the transfer's availability to its recipients ?
            if($data->remind)
                $transfer->remind();
        }
        
        // Need to make the transfer available (sends email to recipients) ?
        if($data->complete)
            $transfer->makeAvailable();
        
        return self::cast($transfer);
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
        // Check parameters
        if(!$id) throw new RestMissingParameterException('transfer_id');
        if(!is_numeric($id)) throw new RestBadParameterException('transfer_id');
        
        // Evaluate security type depending on config and auth
        $security = Config::get('chunk_upload_security');
        if(Auth::isAuthenticated()) $security = 'auth';
        if(($security == 'auth') && !Auth::isAuthenticated())
            throw new RestAuthenticationRequiredException();
        
        $transfer = Transfer::fromId($id);
        
        $user = Auth::user();
        
        if(!$transfer->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'transfer = '.$transfer->id);
        
        // Delete the transfer (not recoverable)
        $transfer->delete();
    }
}
