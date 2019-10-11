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
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
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

/* --------------------------------------------------------
 * download using PHP from a non web accessible folder
 * --------------------------------------------------------
 */
require_once('../includes/init.php');

try {
    // List of files to be downloaded
    if(!array_key_exists('files_ids', $_REQUEST))
        throw new DownloadMissingFilesIDsException();

    $files_ids = array_filter(array_map('trim', explode(',', $_REQUEST['files_ids'])));
    
    if(!count($files_ids))
        throw new DownloadMissingFilesIDsException();
    
    $good_files_ids = array_filter($files_ids, function($id) {
        return preg_match('/^[0-9]+$/', $id) && ((int) $id > 0);
    });
    
    if(count($files_ids) != count($good_files_ids))
        throw new DownloadBadFilesIDsException(array_diff($files_ids, $good_files_ids));
    
    if(array_key_exists('token', $_REQUEST)) {
        // Token on get request
        $token = $_REQUEST['token'];
        
        if(!Utilities::isValidUID($token))
            throw new TokenHasBadFormatException($token);
        
        // Getting recipient from the token
        $recipient = Recipient::fromToken($token); // Throws
        
        // Getting associated transfer 
        $transfer = $recipient->transfer;
        
    } elseif(Auth::isAuthenticated()) {
        // Direct owner/admin download
        if(!Auth::isAdmin()) {
            $not_owned_files = array();
            foreach($files_ids as $fid)
                if(!Auth::user()->is(File::fromId((int)$fid)->transfer->owner))
                    $not_owned_files[] = $fid;
            
            if(count($not_owned_files))
                throw new DownloadBadFilesIDsException($not_owned_files);
        }
        
        $transfer = File::fromId((int)$files_ids[0])->transfer;
        $recipient = null;
                
    } else
        throw new TokenIsMissingException();
    
    // Are all files from the transfer ?
    $not_from_transfer = array();
    foreach($files_ids as $fid)
        if(!File::fromId((int)$fid)->transfer->is($transfer))
            $not_from_transfer[] = $fid;
    
    if(count($not_from_transfer))
        throw new DownloadBadFilesIDsException($not_from_transfer);
    
    // Needed to prevent the download from timing out.
    set_time_limit(0);
    
    // Close session to avoid simultaneous requests from being locked
    session_write_close();
    
    // Check if file set has already been downloaded over the last hour
    $recently_downloaded = $recipient ? AuditLog::clientRecentlyDownloaded($recipient, $files_ids) : false;

    $archive_format_selected = false;
    $archive_format = "zip";
    if( isset($_REQUEST["archive_format"])) {
        if( $_REQUEST["archive_format"] == "zip" ) {
            $archive_format_selected = true;
        }
        if( $_REQUEST["archive_format"] == "tar" ) {
            $archive_format_selected = true;
            $archive_format = "tar";
        }
    }
    
    if(count($files_ids) > 1 || $archive_format_selected) { 
        // Archive download
        $ret = downloadArchive($transfer, $recipient, $files_ids, $recently_downloaded,$archive_format);
    } else {
        // Single file download
        $ret = downloadSingleFile($transfer, $recipient, $files_ids[0], $recently_downloaded);
    }
    
    if($ret['result'] && $recipient)
        manageOptions($ret, $transfer, $recipient, $recently_downloaded);
    
} catch (Exception $e) {
    $storable = new StorableException($e);
    $path = GUI::path() . '?s=exception&exception=' . $storable->serialize();
    header('Location: ' . $path);
}


/**
 * Allows to set an archive to be downloaded
 * 
 * @param Transfer $transfer: the transfer containing the files
 * @param Recipient $recipient
 * @param Array $files_ids: list of files ids
 * 
 * @return boolean: true if succes, false otherwise
 */
function downloadArchive($transfer, $recipient, $files_ids, $recently_downloaded, $archive_format) {

    
    // Creating the zipper
    $zipper = new Archiver($archive_format);
    
    // Adding all files
    $files = array();
    foreach ($files_ids as $fileId) {
        $file = File::fromId($fileId);
        $files[] = $file;
        $zipper->addFile($file);
    }

    $size = -1;
    $time = time();
    
    Logger::info('User started archive download ('.count($files).' files, '.$size.' bytes)');
    
    // Send the ZIP
    if(!$recently_downloaded)
        Logger::logActivity(LogEventTypes::ARCHIVE_DOWNLOAD_STARTED, $transfer, $recipient);
    
    $result = $zipper->streamArchive($recipient);
    
    if(!$recently_downloaded)
        Logger::logActivity(LogEventTypes::ARCHIVE_DOWNLOAD_ENDED, $transfer, $recipient);
    
    Logger::info('User download archive ('.count($files).' files, '.$size.' bytes, '.(time() - $time).' seconds)');
    
    return array('result' => $result, 'files' => $files);
}


/**
 * Allows download a single file
 * 
 * @param Transfer $transfer: the transfer containing the files
 * @param Recipient $reciipient: the recipient of the transfer
 * @param Array $files_ids: list of files ids
 * 
 * @return boolean: true if succes, false otherwise
 */
function downloadSingleFile($transfer, $recipient, $file_id, $recently_downloaded) {
    
    $file = File::fromId($file_id);
    
    if(!$file->transfer->is($transfer))
        throw new FileNotFoundException(array('transfer_id : ' . $transfer->id, 'file_id : ' . $file_id));

    $ranges = null;
    if (array_key_exists('HTTP_RANGE', $_SERVER) && $_SERVER['HTTP_RANGE']) {
        try {
            if (preg_match('/bytes\s*=\s*(.+)$/i', $_SERVER['HTTP_RANGE'], $m)) {
                $parts = array_map('trim', explode(',', $m[1]));
                foreach ($parts as $part) {
                    if (preg_match('/([0-9]+)?(?:-([0-9]+))?/', $part, $m)) {
                        if (!is_numeric($m[1]) && !is_numeric($m[2]))
                            throw new DownloadInvalidRangeException($part);

                        $start = is_numeric($m[1]) ? (int) $m[1] : null;
                        $end = ((count($m) > 2) && is_numeric($m[2])) ? (int) $m[2] : null;

                        if (is_null($end))
                            $end = $file->size;

                        if (is_null($start)) {
                            if ($end > 0) {
                                $start = 0;
                            } else if ($end < 0) {
                                $start = $file - size + $end;
                                $end = $file->size;
                            } else
                                throw new DownloadInvalidRangeException($part); // end can't be O
                        }

                        if ($start > $end)
                            throw new DownloadInvalidRangeException($part); // start can't be after or equal to end

                        $ranges[] = array('start' => $start, 'end' => $end);
                    } else
                        throw new DownloadInvalidRangeException($part);
                }
            } else
                throw new DownloadInvalidRangeException($_SERVER['HTTP_RANGE']);
        } catch (DownloadInvalidRangeException $e) {
            // Send 416 response if invalid range found
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $file->size); // Required in 416.
            exit;
        }
    }

    ob_implicit_flush();

    $abort_handler = function() {
        if (!connection_aborted() && (connection_status() == CONNECTION_NORMAL))
            return;

        Logger::info('Seems that the user stopped downloading (not that reliable though ...)');

        die; // Stop pointless reading if user stopped downloading
    };
    register_shutdown_function($abort_handler);

    $read_range = function($range = null) use($file, $recipient, $abort_handler, $transfer) {
        $abort_handler();

        $offset = $range ? $range['start'] : 0;

        $chunk_size = (int) Config::get('download_chunk_size');
        if (!$chunk_size)
            $chunk_size = 1024 * 1024;

        if($transfer->options['encryption'] == 1){
            $end = $file->encrypted_size;
        }else{
            $end = $file->size;
        }
        if ($range)
            $end = $range['end'];
        
        for (; $offset < $end; $offset += $chunk_size) {
            $remaining = $end - $offset + 1;
            $length = min($chunk_size, $remaining);
            
            Logger::debug('Send chunk at offset ' . $offset . ' with length ' . $length);
            
            echo $file->readChunk($offset, $length);
            
            // TODO Log download progress ?
            
            $abort_handler();
        }
        
        return ($offset >= $file->size);
    };

    if($recipient) $recipient->recordActivity();
    
    $done = false;
    
    $time = time();
    $size = 0;
    
    if ($ranges)
        header('HTTP/1.1 206 Partial Content'); // Must send HTTP header before anything else
    
    header('Content-Transfer-Encoding: binary');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $transfer->created));
    header('ETag: t' . $transfer->id . '_f' . $file->id . '_s' . $file->size);
    header('Connection: close');
    header('Cache-control: private');
    header('Pragma: private');
    header('Expires: 0');
    
    if ($ranges) {
        Logger::info('User restarted download of '.$file.' from offset '.$ranges[0]['start']);
        
        if(!$recently_downloaded)
            Logger::logActivity(LogEventTypes::DOWNLOAD_RESUMED, $file);
        
        if (count($ranges) == 1) { // Single range
            $range = array_shift($ranges);
            
            header('Content-Type: ' . $file->mime_type);
            header('Content-Length: ' . ($range['end'] - $range['start'] + 1));
            header('Content-Range: bytes ' . $range['start'] . '-' . $range['end'] . '/' . $file->size);
            
            // Read range data
            $done = $read_range($range);
            $size += $range['end'] - $range['start'] + 1;
        } else { // Multiple ranges
            $length = 0;
            foreach ($ranges as $range)
                $length += $range['end'] - $range['start'] + 1;
            
            $boundary = 'range_boundary_t' . $transfer->id . '_f' . $file->id . '_s' . $file->size . '_' . uniqid();
            header('Content-Type: multipart/byteranges; boundary=' . $boundary);
            header('Content-Length: ' . $length);
            
            foreach ($ranges as $range) {
                echo '--' . $boundary . "\n";
                echo 'Content-Type: ' . $file->mime_type . "\n";
                echo 'Content-Range: bytes ' . $range['start'] . '-' . $range['end'] . '/' . $file->size . "\n";
                echo "\n";
                
                // Read range data
                $done = $read_range($range);
                $size += $range['end'] - $range['start'] + 1;
                
                echo "\n";
            }
            
            echo '--' . $boundary . '--';
        }
    } else {
        header('Content-Type: ' . $file->mime_type);
        
        // UTF8 filename handling
        $ua = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if(preg_match('`msie (7|8)`i', $ua) && !preg_match('`opera`i', $ua)) {
            // IE7, IE8 but not opera that MAY match
            header('Content-Disposition: attachment; filename='.rawurlencode($file->name));
            
        } else if(preg_match('`android`i', $ua)) {
            // Android OS
            $name = preg_replace('`[^a-z0-9\._\-\+,@£\$€!½§~\'=\(\)\[\]\{\}]`i', '_', $file->name);
            header('Content-Disposition: attachment; filename="'.$name.'"');
            
        } else {
            // All others, see RFC 5987
            header('Content-Disposition: attachment; filename="'.$file->name.'"; filename*=UTF-8\'\''.rawurlencode($file->name));
        }
        
        if($transfer->options['encryption'] == 1){
            header('Content-Length: ' . $file->encrypted_size);
        }else{
            header('Content-Length: ' . $file->size);
        }

        header('Accept-Ranges: bytes');

        // Read data (no range means all file)
        Logger::info('User started to download '.$file);
        
        if(!$recently_downloaded)
            Logger::logActivity(LogEventTypes::DOWNLOAD_STARTED, $file, $recipient);
        
        $read_range();
        $done = true;
        $size += $file->size;
    }
    
    if($done) {
        Logger::info('User downloaded file or file ranges ('.$size.' bytes, '.(time() - $time).' seconds)');
        
        if(!$recently_downloaded)
            Logger::logActivity(LogEventTypes::DOWNLOAD_ENDED, $file, $recipient);
    }
    
    return array('result' => $done, 'files' => array($file));
}


function manageOptions($ret, $transfer, $recipient, $recently_downloaded = false) {
    if ($transfer->getOption(TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE)) {
        if (array_key_exists('notify_upon_completion', $_REQUEST) && (bool) $_REQUEST['notify_upon_completion']) {
            // Notify file download
            ApplicationMail::quickSend('download_complete', $recipient, $ret);
        }
    }
    
    // Only notify owner if client did not download the same set of files over the last
    // period to avoid multiple notifications in case of multiple resume from dumb downloader
    if ($transfer->getOption(TransferOptions::EMAIL_DOWNLOAD_COMPLETE) && !$recently_downloaded) {
        ApplicationMail::quickSend('files_downloaded', $transfer->owner, $ret, array('recipient' => $recipient));
    }
}
