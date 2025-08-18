<?php
/*
 * Store the file using external script
 *
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

if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

require FILESENDER_BASE.'/scripts/client/FilesenderRestClient.class.php';

/**
 * Forwards information to another server
 */
class ForwardAnotherServer
{

    /**
     * forward Transfer
     *
     * @param Transfer $transfer
     */
    public static function forwardTransfer(Transfer $transfer)
    {
        Logger::debug('forward Transfer start');
        $forwarded_transfer = self::createTransfer($transfer);
        self::forwardFiles($transfer);
        return $forwarded_transfer;
    }

    /**
     * forward Transfer
     *
     * @param Transfer $transfer
     *
     */
    private static function forwardFiles(Transfer $transfer)
    {
        if (!isset($transfer->forward_server['method'])) {
            throw new ForwardException('Not set forward method');
        }
        $method = $transfer->forward_server['method'];
        $script = FILESENDER_BASE.'/scripts/StorageFilesystemForward/forward-'.$method.'.php';
        if (!file_exists($script)) {
            throw new ForwardException('Not found forward script');
        }

        $output = '/dev/null';
        $id = $transfer->id;
        $server = self::getServerByTransfer($transfer);
        if (!$server) {
            throw new ForwardException('Not found server: '.$transfer->forward_server);
        }
        $hostname = $server['hostname'];
        Logger::logActivity(LogEventTypes::FORWARD_STARTED, $transfer);
        Logger::debug('forward script exec start, id=' . $id);
        Logger::debug("exec: nohup php $script $id $hostname >> $output 2>&1 &");
        exec("nohup php $script $id $hostname >> $output 2>&1 &");
        Logger::debug('forward script executed asynchronously');
        Logger::info('Transfer#' . $id . ' is beeing sent to ' . $hostname);
    }

    /**
     * setup REST API client
     *
     * @param Transfer $transfer
     */
    private static function setupRestClient(Transfer $transfer)
    {
        $url = rtrim($transfer->getForwardedServer(), '/') . '/rest.php';
        $appname = $transfer->forward_server['appname'];
        $applications = Config::get('auth_remote_applications');
        if (!is_array($applications) ||
            !isset($applications[$appname]) ||
            !isset($applications[$appname]['secret'])) {
            throw new ForwardException('Not found application: '.$appname);
        }
        $secret = $applications[$appname]['secret'];
        return new FilesenderRestClient($url, 'application', $appname, $secret);
    }

    /**
     * get Forward server information
     *
     * @param string server_id
     */
    public static function getServersList()
    {
        return Config::get('forward_server_list');
    }

    /**
     * get Forward server information
     *
     * @param string server_id
     */
    public static function getServer($server_id)
    {
        if (!$server_id || !is_string($server_id)) return null;
        $servers = self::getServersList();
        if (array_key_exists($server_id, $servers)) {
            return $servers[$server_id];
        }
        return null;
    }

    /**
     * get Forward server information
     *
     * @param string appname
     * @param string method
     */
    public static function getServerByApp($appname, $method)
    {
        if (!$appname || !is_string($appname) ||
            !$method || !is_string($method)) return null;
        $servers = self::getServersList();
        foreach ($servers as $server_id => $server) {
            if ($server['appname'] === $appname &&
                $server['method'] === $method) {
                return $server;
            }
        }
        return null;
    }

    /**
     * get Forward server information
     *
     * @param Transfer $transfer
     */
    public static function getServerByTransfer(Transfer $transfer)
    {
        if (!isset($transfer->forward_server['appname']) ||
            !isset($transfer->forward_server['method'])) return null;
        $appname = $transfer->forward_server['appname'];
        $method = $transfer->forward_server['method'];
        return self::getServerByApp($appname, $method);
    }

    /**
     * get Forward server label
     *
     * @param string server_id
     */
    public static function getServerLabel($server_id)
    {
        if (!$server_id || !is_string($server_id)) return null;
        $servers = self::getServersList();
        if (!array_key_exists($server_id, $servers)) {
            return null;
        }
        $server = $servers[$server_id];
        $server_label = $server['label'];
        if (is_array($server_label)) {
            $userlang = Lang::getCode();
            if (isset($server_label[$userlang])) {
                $server_label = $server_label[$userlang];
            } else {
                $server_label = current($server_label);
            }
        }
        return $server_label;
    }

    /**
     * get Storage Class
     *
     * @param Transfer $transfer
     */
    public static function getStorageClass(Transfer $transfer)
    {
        if (!isset($transfer->forward_server['method'])) {
            throw new ForwardException('Not set forward method');
        }
        $method = $transfer->forward_server['method'];
        $class = 'StorageFilesystemForward'.$method;
        if (!class_exists($class)) {
            $class = null;
        }
        return $class;
    }

    /**
     * Create Transfer on destination server
     * Call API: POST /transfer
     *
     */
    public static function createTransfer($transfer)
    {
        Logger::debug('call REST API: POST /transfer');
        $client = self::setupRestClient($transfer);

        $files = array();
        foreach( $transfer->files as $f ) {
            array_push($files, array(
                'name' => $f->name,
                'size' => $f->size,
                'mime_type' => $f->mime_type,
                'iv' => $f->iv,
                'aead' => $f->aead,
                'forward_id' => $f->id,
            ));
        }
        $recipients = array();
        foreach( $transfer->recipients as $r ) {
            array_push($recipients, $r->email);
        }
        $options = array(
            TransferOptions::GET_A_LINK => 0,
            TransferOptions::FORWARD_TO_ANOTHER_SERVER => 0,
            TransferOptions::EMAIL_ME_COPIES => 0,
            TransferOptions::EMAIL_ME_ON_EXPIRE => 0,
            TransferOptions::EMAIL_UPLOAD_COMPLETE => 0,
            TransferOptions::EMAIL_DOWNLOAD_COMPLETE => 0,
            TransferOptions::EMAIL_DAILY_STATISTICS => 0,
            TransferOptions::EMAIL_REPORT_ON_CLOSING => 0,
            TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE => 0,
            TransferOptions::ADD_ME_TO_RECIPIENTS => 0,
            TransferOptions::EMAIL_RECIPIENT_WHEN_TRANSFER_EXPIRES => 0,
        );
        $content = array(
            'from' => $transfer->user_email,
            'files' => $files,
            'recipients' => $recipients,
            'subject' => $transfer->subject,
            'message' => $transfer->message,
            'expires' => $transfer->expires,
            'aup_checked' => 1,
            'forward_server' => array(
                'appname' => $transfer->forward_server['appname'],
                'method' => $transfer->forward_server['method'],
                'from' => Config::get('site_url')),
            'forward_id' => $transfer->id,
            'options' => $options,
            'encryption' => $transfer->options['encryption'],
            'encryption_key_version' => $transfer->key_version,
            'encryption_password_encoding' => $transfer->password_encoding,
            'encryption_password_version' => $transfer->password_version,
            'encryption_password_hash_iterations' => $transfer->password_hash_iterations,
            'encryption_client_entropy' => $transfer->client_entropy,
            'encryption_salt' => $transfer->salt,
        );

        $tries = 0;
        $max_tries = 10;
        $response = null;
        do {
            try {
                Logger::debug('content: '.json_encode($content, JSON_PRETTY_PRINT));
                $r = $client->post('/transfer', null, $content);
                $response = $r->created;
                Logger::debug('end REST: POST /transfer; response->id = ' . $response->id);
                $transfer->forward_id = $response->id;
                foreach( $response->files as $dest ) {
                    if (! File::unicityUid($dest->uid, $tries)) {
                        Logger::debug('File::unicityUid($dest->uid, $tries): ' . $tries);
                        $tries++;
                        throw new ForwardFileUidExistException();
                    }
                }
                foreach( $response->recipients as $dest ) {
                    if (! Recipient::unicityToken($dest->token, $tries)) {
                        Logger::debug('Recipient::unicityUid($dest->uid, $tries): ' . $tries);
                        $tries++;
                        throw new ForwardFileUidExistException();
                    }
                }
                break;
            } catch (ForwardFileUidExistException $e) {
                $response = null;
                self::deleteTransfer($transfer);
            }
        } while(!$response && $tries <= $max_tries);
        // Fail if max tries reached
        if ($tries > $max_tries) {
            throw new ForwardFileUidExistException();
        }

        // rename to the destination filename
        $storage_class_name = self::getStorageClass($transfer);
        foreach( $transfer->files as $src ) {
            foreach( $response->files as $dest ) {
                if ($src->name == $dest->name) {
                    Logger::debug('file uid: ' . $src->uid . ' => ' . $dest->uid);
                    $src_path = Storage::buildPath($src) . $src->uid;
                    $dest_path = Storage::buildPath($src) . $dest->uid;
                    if (!rename($src_path, $dest_path)) {
                        throw new ForwardFileCannotRenameException($src->uid, $dest->uid);
                    }
                    $src->uid = $dest->uid;
                    $src->forward_id = $dest->id;
                    if ($storage_class_name) $src->storage_class_name = $storage_class_name;
                    $src->save();
                    break;
                }
            }
            if (!$src->forward_id) {
                throw new ForwardException('response file name not exist: '.$dest->name);
            }
        }

        foreach( $transfer->recipients as $src ) {
            foreach( $response->recipients as $dest ) {
                if ($src->email == $dest->email) {
                    Logger::debug('recipients token: ' . $src->token . ' => ' . $dest->token);
                    $src->forward_id = $dest->id;
                    $src->token = $dest->token;
                    $src->save();
                    break;
                }
            }
            if (!$src->forward_id) {
                throw new ForwardException('response recipient name not exist: '.$dest->email);
            }
        }

        return $transfer;
    }

    /**
     * Complete Transfer on destination server
     * Call API: PUT /transfer/{id}, payload: {complete: true}
     *
     */
    public static function completeTransfer($transfer)
    {
        Logger::debug('call REST API: PUT /transfer - complete');
        $client = self::setupRestClient($transfer);

        $content = array(
            'complete' => 1
        );
        Logger::debug($client);
        Logger::debug($transfer->forward_id);
        Logger::debug($content);
        $r = $client->put('/transfer/'.$transfer->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: PUT /transfer');
        return $response;
    }

    /**
     * Close Transfer on destination server
     * Call API: PUT /transfer/{id}, payload: {closed: true}
     *
     */
    public static function closeTransfer($transfer)
    {
        Logger::debug('call REST API: PUT /transfer - closed');
        $client = self::setupRestClient($transfer);

        $content = array(
            'closed' => 1
        );
        $r = $client->put('/transfer/'.$transfer->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: PUT /transfer');
        return $response;
    }

    /**
     * Delete Transfer on destination server
     * Call API: DELETE /transfer/{id}
     *
     */
    public static function deleteTransfer($transfer)
    {
        Logger::debug('call REST API: DELETE /transfer');
        $client = self::setupRestClient($transfer);

        $content = array(
        );
        $r = $client->delete('/transfer/'.$transfer->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: DELETE /transfer');
        return $response;
    }

    /**
     * Extend expiry data on destination server
     * Call API: PUT /transfer/{id}/extend_expiry_date/1742914800
     *
     */
    public static function extendTransferExpiryDate($transfer, string $expires)
    {
        Logger::debug('call REST API: PUT /transfer - extend_expiry_date:'.$expires);
        $client = self::setupRestClient($transfer);

        $content = array(
            'extend_expiry_date' => $expires
        );
        $r = $client->put('/transfer/'.$transfer->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: PUT /transfer');
        return $response;
    }

    /**
     * Add Recipient to the Transfer on destination server
     * Call API: POST /transfer/{id}/recipient
     *
     */
    public static function addRecipient($transfer, string $email)
    {
        Logger::debug('call REST API: POST /transfer - add recipient');
        $client = self::setupRestClient($transfer);

        $content = array(
            'recipient' => $email
        );
        $r = $client->post('/transfer/'.$transfer->forward_id.'/recipient', null, $content);
        $response = $r->created;
        Logger::debug('end REST: POST /transfer');
        return $response;
    }

    /**
     * Delete Recipient from the Transfer on destination server
     * Call API: DELETE /recipient/{id}
     *
     */
    public static function deleteRecipient(Recipient $recipient)
    {
        Logger::debug('call REST API: POST /transfer - add recipient');
        $client = self::setupRestClient($recipient->$transfer);

        $content = array(
        );
        $r = $client->delete('/recipient/'.$recipient->forward_id, null, $content);
        $response = $r;
        $recipient->forward_id = null;
        Logger::debug('end REST: DELETE /recipient');
        return $response;
    }

    /**
     * Put a file chunk
     *
     * @param File $file
     * @param blob chunk
     * @param int offset
     */
    public static function putFileChunk(File $file, $chunk, $offset)
    {
        Logger::debug('call REST API: PUT /file');
        $client = self::setupRestClient($file->transfer);

        $args = array(
            'key' => $file->uid,
        );
        $options = array(
            'Content-Type' => 'application/octet-stream',
        );
        $r = $client->put('/file/'.$file->forward_id.'/chunk/'.$offset, $args, $chunk, $options);
        $response = $r;
        return $response;
    }

    /**
     * Handles file completion checks
     * Call API: PUT /file/{id}, payload: {complete: true}
     *
     * @param File $file
     */
    public static function completeFile(File $file)
    {
        Logger::debug('call REST API: PUT /file');
        $client = self::setupRestClient($file->transfer);

        $content = array(
            'complete' => 1
        );
        $r = $client->put('/file/'.$file->forward_id, null, $content);
        $response = $r;
        return $response;
    }

    /**
     * Deletes a file
     * Call API: DELETE /file/{id}
     *
     * @param File $file
     *
     * @throws StorageFilesystemCannotDeleteException
     */
    public static function deleteFile(File $file)
    {
        Logger::debug('call REST API: DELETE /file');
        $client = self::setupRestClient($file->transfer);

        $content = array(
        );
        $r = $client->delete('/file/'.$file->forward_id, null, $content);
        $response = $r;
        $file->forward_id = null;
        return $response;
    }

    /**
     * Record Activity
     * Call API: PUT /transfer/{id}/record_activity/{event}
     *
     */
    public static function recordActivityTransfer(string $event, Transfer $transfer, ?Auditlog $auditlog = null, ?Recipient $recipient = null, $files = null)
    {
        Logger::debug('call REST API: PUT /transfer - record_activity:'.$event);
        $client = self::setupRestClient($transfer);

        $fileids = array();
        if (is_array($files)) {
            foreach( $files as $file ) {
                $fileids[] = $file->id;
            }
        }

        $content = array(
            'record_activity' => $event,
            'created' => ($auditlog ? $auditlog->created : time()),
            'ip' => Utilities::getClientIP(), //($auditlog ? $auditlog->ip : Utilities::getClientIP()),
            'author' => ($auditlog ? $auditlog->author : ($recipient ? $recipient->email : null)),
            'fileids' => $fileids,
        );
        $r = $client->put('/transfer/'.$transfer->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: PUT /transfer');
        return $response;
    }

    /**
     * Record Activity
     * Call API: PUT /file/{id}/record_activity/{event}
     *
     */
    public static function recordActivityFile(string $event, File $file, ?Auditlog $auditlog = null, ?Recipient $recipient = null)
    {
        Logger::debug('call REST API: PUT /file - record_activity:'.$event);
        $client = self::setupRestClient($file->transfer);

        $content = array(
            'record_activity' => $event,
            'created' => ($auditlog ? $auditlog->created : time()),
            'ip' => Utilities::getClientIP(), //($auditlog ? $auditlog->ip : Utilities::getClientIP()),
            'author' => ($auditlog ? $auditlog->author : ($recipient ? $recipient->email : null)),
        );
        $r = $client->put('/file/'.$file->forward_id, null, $content);
        $response = $r;
        Logger::debug('end REST: PUT /file');
        return $response;
    }

}
