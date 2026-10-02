<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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

require_once(dirname(__FILE__).'/../../includes/init.php');

Logger::info('Local transfer creation started');

$transfer = null;

try {
    if(!function_exists('finfo_open'))
        throw new Exception('File Info PHP extention is required but not found');
    
    // Get args
    $args = array(
        'h' => 'help',
        'u:' => 'user_id:',
        'f:' => 'from:',
        'r:' => 'recipient:',
        's:' => 'subject:',
        'e:' => 'expires:',
        'm:' => 'message:',
        'o:' => 'option:',
        'l' => 'link_instead_of_copy',
        'v' => 'verbose'
    );
    
    $opts = getopt(implode('', array_keys($args)), $args);
    
    $be_verbose = array_key_exists('v', $opts) || array_key_exists('verbose', $opts);
    
    $verbose = function($msg) use($be_verbose) {
        if(!$be_verbose) return;
        
        echo $msg."\n";
    };
    
    // Print help if no args or help wanted
    if(array_key_exists('h', $opts) || array_key_exists('help', $opts) || !count(array_slice($argv, 1))) {
        echo 'Create FileSender file transfer using local files.'."\n\n";
        echo 'Usage '.basename(__FILE__).' -u|--user_id=<uid> [-f|--from=<email>] [-e|--expires=<days>] [-s|--subject=<text>] [-m|--message=<message>] [-o|--options=<option_name>] -r|--recipient=<email> [-l|--link_instead_of_copy] <file> [<file>]'."\n";
        echo "\t".'-u|--user_id the FileSender user identifier, similar to the auth_sp_uid_attribute'."\n";
        echo "\t\n";
        echo "\t".'-f|--from the email to use as sender, if not provided the user identifier will be used'."\n";
        echo "\t\n";
        echo "\t".'-e|--expires the expiration date as a number of days, if not provided the configured default duration will be used'."\n";
        echo "\t\n";
        echo "\t".'-s|--subject the subject of the transfer, optionnal'."\n";
        echo "\t\n";
        echo "\t".'-m|--message the message to sent along with the download link, optionnal, if value is "-" standard input will be fetched'."\n";
        echo "\t\n";
        echo "\t".'-o|--option transfer option, may be used several times, possible values can be found in classes/constants/TransferOptions.class.php'."\n";
        echo "\t\n";
        echo "\t".'-r|--recipient recipient email address, may be used several times'."\n";
        echo "\t\n";
        echo "\t".'--link_instead_of_copy create symlinks in storage instead of copying the file(s) (if storage supports it).'."\n";
        echo "\t\n";
        echo "\t".'<file> : file(s) path(s), if directory all files within will be uploaded.'."\n";
        exit(0);
    }
    
    // Fetch args after last option
    $paths = array();
    while($f = array_pop($argv)) {
        if(substr($f, 0, 1) == '-') break;
        
        array_unshift($paths, $f);
    }
    
    // Remove last fetched path if previous token is a value taking option
    if($f && $f != '-l' && $f != '--link_instead_of_copy')
        array_shift($paths);
    
    // Merge short options into long ones
    foreach($args as $short => $long) {
        $short = str_replace(':', '', $short);
        $long = str_replace(':', '', $long);
        
        if(!array_key_exists($short, $opts)) continue;
        
        if(array_key_exists($long, $opts)) {
            $opts[$long] = array_merge((array)$opts[$long], (array)$opts[$short]);
            
        } else {
            $opts[$long] = $opts[$short];
        }
        
        unset($opts[$short]);
    }
    
    // Sanity checks
    $getarg = function($name, $multiple = false, $fatal = false) use($opts) {
        if(!array_key_exists($name, $opts) || (!is_bool($opts[$name] && !$opts[$name]))) {
            if($fatal)
                throw new Exception('No '.$name.' provided');
            
            return $multiple ? array() : null;
        }
        
        $value = array_map(function($v) {
            return is_bool($v) ? true : $v;
        }, (array)$opts[$name]);
        
        if(!count(array_filter($value))) {
            if($fatal)
                throw new Exception('No '.$name.' provided');
            
            return $multiple ? array() : null;
        }
        
        if(!$multiple)
            $value = array_shift($value);
        
        return $value;
    };
    
    // User identifier
    $uid = $getarg('user_id', false, true);
    $verbose('UID : '.$uid);
    
    // Sender address
    $from = $getarg('from');
    if(!$from) $from = $uid;
    if(!Utilities::validateEmail($from))
        throw new Exception('Not a valid from (email address expected) : '.$from);
    $verbose('From : '.$from);
    
    // Expiration in days
    $expires = (int)$getarg('expires');
    if(!$expires)
        $expires = Config::get('default_transfer_days_valid');
    if($expires < 1 || $expires > Config::get('max_transfer_days_valid'))
        throw new Exception('Expires is out of bounds (1 .. '.Config::get('max_transfer_days_valid').')');
    $verbose('Expiry : '.$expires.' days');
    
    // Message
    $subject = $getarg('subject');
    $verbose('Subject : '.$subject);
    
    $message = $getarg('message');
    if($message == '-') $message = trim(file_get_contents('php://stdin'));
    $verbose('Message : '.(mb_strlen($message) > 32 ? mb_substr($message, 0, 32).' ... ('.mb_strlen($message).' characters long)' : $message));
    
    // Transfer options
    $options = $getarg('option', true);
    foreach($options as $option)
        if(!TransferOptions::isValidValue($option))
            throw new Exception('Unknown transfer option : '.$option);
    
    $verbose('Options : '.implode(', ', $options));
    
    // Recipients
    $recipients = $getarg('recipient', true, true);
    foreach($recipients as $recipient)
        if(!Utilities::validateEmail($recipient))
            throw new Exception('Not a valid recipient (email address expected) : '.$recipient);
    
    if(count($recipients) > Config::get('max_transfer_recipients'))
        throw new Exception('Too many recipients (1 .. '.Config::get('max_transfer_recipients').')');
    
    $verbose('Recipients : '.implode(', ', $recipients));
    
    // File mode
    $link_instead_of_copy = $getarg('link_instead_of_copy');
    
    if($link_instead_of_copy) {
        if(Storage::supportsLinking()) {
            $verbose('Storage supports linking, links to files will be created instead of copying them');
        } else {
            $verbose('Storage does not supports linking, standard storing procedure will be used');
            $link_instead_of_copy = false;
        }
    }
    
    if(!$link_instead_of_copy) {    
        if(Storage::supportsWholeFile()) {
            $verbose('Storage supports whole file processing, files will be stored by copying them in a single operation');
            
        } else {
            $verbose('Storage does not support whole file processing, files will be stored chunk by chunk');
        }
    }
    
    // Gather files
    $files = array();
    foreach($paths as $path) {
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
    $files = array_unique($files);
    
    if(count($files) > Config::get('max_transfer_files'))
        throw new Exception('Too many files (1 .. '.Config::get('max_transfer_files').')');
    
    $verbose('Files :'."\n\t".implode(",\n\t", $files));
    
    
    // Open local session
    AuthLocal::setUser($uid, $from);
    $verbose('User session openned');
    
    // Create the transfer
    $transfer = Transfer::create(time() + $expires * 24 * 3600);
    
    // Add subject/message
    if($subject) $transfer->subject = $subject;
    if($message) $transfer->message = $message;
    
    // Add options
    if($options) $transfer->options = $options;
    
    // Save transfer
    $transfer->save();
    
    $verbose('Empty transfer created');
    
    // Add files
    $verbose('Adding files ...');
    $info = finfo_open(FILEINFO_MIME_TYPE);
    foreach($files as $path) {
        $path = realpath($path);
        $mime = finfo_file($info, $path);
        $file = $transfer->addFile(basename($path), filesize($path), $mime);
        
        $verbose('Adding '.$path.' ('.$mime.', '.filesize($path).' bytes) ... ');
        
        if($link_instead_of_copy && Storage::supportsLinking()) {
            Storage::storeAsLink($file, $path);
            
        } else if(Storage::supportsWholeFile()) {
            Storage::storeWholeFile($file, $path);
            
        } else {
            $chunk_size = Config::get('upload_chunk_size');
            if($fh = fopen($path, 'rb')) {
                for($offset=0; $offset<=$file->size; $offset+=$chunk_size) {
                    $data = fread($fh, $chunk_size);
                    $file->writeChunk($data, $offset);
                    $verbose('Chunk '.$offset.'..'.($offset + $chunk_size).' added');
                }
                
                fclose($fh);
            } else throw new CoreCannotReadFileException($path);
        }
        
        $file->complete();
        $verbose('Done for '.$path);
    }
    finfo_close($info);
    
    $verbose('All files added');
    
    // Add recipients
    foreach($recipients as $recipients)
        $transfer->addRecipient($recipients);
    
    $verbose('Recipients added');
    
    // Make transfer available
    $transfer->makeAvailable();
    
    $verbose('Transfer made available');
    
} catch(Exception $e) {
    Logger::error('Local transfer creation failed : '.$e->getMessage());
    
    // Cleanup
    if($transfer) $transfer->delete();
    
    die($e->getMessage()."\n\n");
}

exit(0);
