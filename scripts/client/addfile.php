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

require_once(dirname(dirname(__FILE__)).'/includes/init.php');

Logger::info('Command line "uploader" started');

$transfer = null;

try {
    if(!function_exists('finfo_open'))
        throw new Exception('File Info PHP extention is required but not found');
    
    $args = array(
        'user_id' => null,
        'from' => null,
        'files' => array(),
        'recipients' => array(),
        'subject' => null,
        'expires' => null,
        'message' => null,
        'options' => array()
    );
    
    $help = false;
    
    
    // Fetch arguments
    
    foreach(array_slice($argv, 1) as $arg) {
        if(!preg_match('`--([^=]+)(?:=(.+))?$`', $arg, $m))
            throw new Exception('Argument corrupted : '.$arg."\n");
        
        $arg = $m[1];
        $value = (count($m) > 2) ? $m[2] : null;
        
        if($arg == 'help')
            $help = true;
        
        if(!array_key_exists($arg, $args))
            throw new Exception('Argument is unknown : '.$arg."\n");
        
        if(is_array($args[$arg])) {
            $args[$arg] = array_merge($args[$arg], explode(',', $value));
        } else {
            $args[$arg] = $value;
        }
    }
    
    if($help || !count(array_slice($argv, 1))) {
        echo 'Put local files under filesender.'."\n\n";
        echo 'usage '.basename(__FILE__).' --user_id=<uid> [--from=<email>] [--expires=<days>] [--subject=<text>] [--message=<message>] [--options=<option_name>[,<option_name>]] --recipients=<email>[,<email>] --file=<path>[,<path>]'."\n";
        echo "\t".'If no --from is provided --user_id will be used as sender address.'."\n";
        echo "\t".'If no --expires is provided configured default duration will be used.'."\n";
        echo "\t".'--options possible values can be found in classes/constants/TransferOptions.class.php'."\n";
        echo "\t".'--files paths can point to directories so that all files within will be uploaded.'."\n";
        exit(0);
    }
    
    
    // Sanity checks
    
    if(!$args['user_id'])
        throw new Exception('No user id provided'."\n");
    
    if(!$args['from']) $args['from'] = $args['user_id'];
    
    if(!$args['expires'])
        $args['expires'] = Config::get('default_transfer_days_valid');
    $args['expires'] = (int)$args['expires'];
    if($args['expires'] < 1 || $args['expires'] > Config::get('max_transfer_days_valid'))
        throw new Exception('Expires is out of bounds (1 .. '.Config::get('max_transfer_days_valid').')'."\n");
    
    foreach($args['options'] as $option)
        if(!TransferOptions::isValidValue($option))
            throw new Exception('Unknown transfer option : '.$option."\n");
    
    if(!filter_var($args['from'], FILTER_VALIDATE_EMAIL))
        throw new Exception('Not a valid from (email address expected) : '.$args['from']."\n");
    
    foreach($args['recipients'] as $recipient)
        if(!filter_var($recipient, FILTER_VALIDATE_EMAIL))
            throw new Exception('Not a valid recipient (email address expected) : '.$recipient."\n");
    
    if(count($args['recipients']) > Config::get('max_transfer_recipients'))
        throw new Exception('Too many recipients (1 .. '.Config::get('max_transfer_recipients').')');
    
    $files = array();
    foreach($args['files'] as $path) {
        if(is_dir($path)) {
            if(substr($path, -1) != '/') $path .= '/';
            
            foreach(scandir($path) as $i)
                if(is_file($path.$i))
                    $files[] = $path.$i;
            
        } else if(is_file($path)) {
            $files[] = $path;
            
        } else
            throw new Exception('Not a valid path : '.$path."\n");
    }
    $args['files'] = array_unique($files);
    
    if(count($args['files']) > Config::get('max_transfer_files'))
        throw new Exception('Too many files (1 .. '.Config::get('max_transfer_files').')');
    
    
    // Open local session
    AuthLocal::setUser($args['user_id'], $args['from']);
    
    // Create the transfer
    $transfer = Transfer::create(time() + $args['expires'] * 24 * 3600);
    
    // Add subject/message
    if($args['subject']) $transfer->subject = $args['subject'];
    if($args['message']) $transfer->message = $args['message'];
    
    // Add options
    if($args['options']) $transfer->options = $args['options'];
    
    // Save transfer
    $transfer->save();
    
    // Add files
    $info = finfo_open(FILEINFO_MIME_TYPE);
    foreach($args['files'] as $path) {
        $mime = finfo_file($info, $path);
        $file = $transfer->addFile(basename($path), filesize($path), $mime);
        
        if(Storage::supportsWholeFile()) {
            Storage::storeWholeFile($file, $path);
            
        } else {
            $chunk_size = Config::get('upload_chunk_size');
            if($fh = fopen($path, 'rb')) {
                for($offset=0; $offset<=$file->size; $offset+=$chunk_size) {
                    $data = fread($fh, $chunk_size);
                    $file->writeChunk($data, $offset);
                }
                
                fclose($fh);
            } else throw new CoreCannotReadFileException($path);
        }
    }
    finfo_close($info);
    
    // Add recipients
    foreach($args['recipients'] as $recipients)
        $transfer->addRecipient($recipients);
    
    // Make transfer available
    $transfer->makeAvailable();
    
} catch(Exception $e) {
    Logger::error($input.' processing failed : '.$e->getMessage());
    
    if($transfer) $transfer->delete();
    
    die($e->getMessage()."\n");
}

exit(0);
