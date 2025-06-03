<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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

// UID for backups
$uid = uniqid();

// Backup old data BEFORE database structure upgrade
Upgrader::register('pre', function() use($uid) {
    // Move old data to backup tables
    echo 'Backuping table files'."\n";
    DBI::prepare('CREATE TABLE files_'.$uid.' AS (SELECT * FROM files)')->execute();
    
    echo 'Backuping table logs'."\n";
    DBI::prepare('CREATE TABLE logs_'.$uid.' AS (SELECT * FROM logs)')->execute();
    
    // Drop old tables so that new ones with eventually the same name are created without problem
    echo 'Droping old files table'."\n";
    DBI::prepare('DROP TABLE files')->execute();
    
    echo 'Droping old logs table'."\n";
    DBI::prepare('DROP TABLE logs')->execute();
});

// Convert data AFTER database structure upgrade
Upgrader::register('post', function() use($uid) {
    // Convert data
    $guest_vouchers = array();
    $filestrid_to_guest = array();
    $fileuid_to_transfer = array();
    
    // Get guest vouchers
    $s = DBI::prepare('SELECT * FROM files_'.$uid.' WHERE filestatus="Voucher" OR filestatus="Voucher Cancelled"');
    $s->execute();
    
    foreach($s->fetchAll() as $record)
        $guest_vouchers[$record['filevoucheruid']] = $record;
    
    $bstrid = function($record) {
        $data = array('filefrom', 'filesize', 'fileoriginalname', 'fileip4address', 'fileip6address', 'fileauthuseruid');
        foreach(array() as $k) $data[] = $record[$k];
        return implode('|', $data);
    };
    
    // Match file uids to guest who created them
    $s = DBI::prepare('SELECT * FROM files_'.$uid.' WHERE filestatus="Closed"');
    $s->execute();
    
    foreach($s->fetchAll() as $record) {
        if(!preg_match('/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/', $record['fileuid'])) continue;
        
        // Guest created
        
        if(!array_key_exists($record['fileuid'], $guest_vouchers)) continue; // No match ...
        
        $filestrid_to_guest[$bstrid[$record]] = $guest_vouchers[$record['fileuid']];
    }
    
    $guest_vouchers = null; // Free memory ...
    
    
    // Recreate transfers
    $s = DBI::prepare('SELECT * FROM files_'.$uid.' WHERE filestatus="Available"');
    $s->execute();
    
    foreach($s->fetchAll() as $record) {
        if(!array_key_exists($record['fileuid'], $fileuid_to_transfer)) {
            // Another recipient from an existing transfer
            
            $transfer = $fileuid_to_transfer[$record['fileuid']];
        } else {
            AuthLocal::setUser($record['fileauthuseruid'], $record['fileauthuseremail']);
            
            $transfer = Transfer::create(strtotime($record['fileexpirydate']));
            $strid = $bstrid($record);
            
            if(array_key_exists($strid, $filestrid_to_guest)) {
                // This is from a guest
                $g = $filestrid_to_guest[$strid];
                
                $guest = Guest::create($g['fileto']);
                $guest->subject = $g['filesubject'];
                $guest->message = $g['filemessage'];
                $guest->save();
                
                $gs = DBI::prepare('UPDATE '.Guest::getDBTable().' SET token=:token, status=:status, created=:created, expires=:expires, last_activity=:last_activity WHERE id=:id');
                $gs->execute(array(
                    ':id' => $guest->id,
                    ':token' => $g['filevoucheruid'],
                    ':created' => $g['filecreateddate'],
                    ':expires' => $g['fileexpirydate'],
                    ':last_activity' => $g['fileactivitydate'],
                    ':status' => ($g['filestatus'] == 'Closed') ? 'closed' : 'available'
                ));
                
                $transfer->guest = $guest;
            }
            
            $transfer->subject = $record['filesubject'];
            $transfer->message = $record['filemessage'];
            $transfer->status = TransferStatuses::AVAILABLE;
            
            $options = array();
            
            if($record['filedownloadconfirmations']) $options[] = TransferOptions::EMAIL_DOWNLOAD_COMPLETE;
            if($record['fileenabledownloadreceipts']) $options[] = TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE;
            if($record['filedailysummary']) $options[] = TransferOptions::EMAIL_DAILY_STATISTICS;
            
            $transfer->save();
            
            $gs = DBI::prepare('UPDATE '.Transfer::getDBTable().' SET created=:created, expires=:expires WHERE id=:id');
            $gs->execute(array(
                ':id' => $transfer->id,
                ':created' => $record['filecreateddate'],
                ':expires' => $record['fileexpirydate']
            ));
            
            $file = $transfer->addFile($record['fileoriginalname'], $record['filesize']);
            
            $fs = DBI::prepare('UPDATE '.File::getDBTable().' SET uid=:uid WHERE id=:id');
            $fs->execute(array(
                ':id' => $file->id,
                ':uid' => $record['fileuid'],
                ':expires' => $record['fileexpirydate']
            ));
        }
        
        // Add recipient
        $recipient = $transfer->addRecipient($record['fileto']);
        
        $rs = DBI::prepare('UPDATE '.Recipient::getDBTable().' SET token=:token, created=:created WHERE id=:id');
        $rs->execute(array(
            ':id' => $recipient->id,
            ':token' => $record['filevoucheruid'],
            ':created' => $record['filecreateddate']
        ));
    }
    
    
    // Drop backups
    echo 'Droping files backup table'."\n";
    DBI::prepare('DROP TABLE files_'.$uid)->execute();
    
    echo 'Droping logs backup table'."\n";
    DBI::prepare('DROP TABLE logs_'.$uid)->execute();
});

// Move and rename lang strings in custom config files
Upgrader::register('post', function() use($uid) {
    $renamed = array(
        '_DP_closeText' => 'dp_close_text',
        '_DP_currentText' => 'dp_current_text',
        '_DP_dayNames' => 'dp_day_names',
        '_DP_dayNamesMin' => 'dp_day_names_min',
        '_DP_dayNamesShort' => 'dp_day_names_short',
        '_DP_firstDay' => 'dp_first_day',
        '_DP_isRTL' => 'dp_is_rtl',
        '_DP_monthNames' => 'dp_month_names',
        '_DP_monthNamesShort' => 'dp_month_names_short',
        '_DP_nextText' => 'dp_next_text',
        '_DP_prevText' => 'dp_prev_text',
        '_DP_showMonthAfterYear' => 'dp_show_month_after_year',
        '_DP_weekHeader' => 'dp_week_header',
        '_DP_yearSuffix' => 'dp_year_suffix',
        '_DP_dateFormat' => 'dp_date_format',
        
        '_ABOUT' => 'about',
        '_ADD_ME_TO_RECIPIENTS' => 'add_me_to_recipients',
        '_ADVANCED_SETTINGS' => 'advanced_settings',
        '_CANCEL' => 'cancel',
        '_CLEAR_ALL' => 'clear_all',
        '_CLOSE' => 'close',
        '_CREATED' => 'created',
        '_DELETE' => 'delete',
        '_DETAILS' => 'details',
        '_DOWNLOAD' => 'download',
        '_DOWNLOADS' => 'downloads',
        '_DRAG_AND_DROP' => 'drag_and_drop',
        '_EMAIL_SENT' => 'email_sent',
        '_EMAIL_SEPARATOR_MSG' => 'email_separator_msg',
        '_ENTER_TO_EMAIL' => 'enter_to_email',
        '_EXPIRY_DATE' => 'expiry_date',
        '_FILES' => 'files',
        '_FROM' => 'from',
        '_HELP' => 'help',
        '_INVALID_FILE' => 'invalid_file',
        '_LOGON' => 'logon',
        '_MESSAGE' => 'message',
        '_NO' => 'no',
        '_NUMBER_OF_FILES' => 'number_of_files',
        '_OK' => 'ok',
        '_OPTIONAL' => 'optional',
        '_PAUSE' => 'pause',
        '_RECIPIENTS' => 'recipients',
        '_SELECT_FILE' => 'select_file',
        '_SELECT_FILES' => 'select_files',
        '_SEND' => 'send',
        '_SEND_NEW_VOUCHER' => 'send_new_voucher.html.php',
        '_SEND_VOUCHER' => 'send_voucher',
        '_SHOWHIDE' => 'showhide',
        '_SITE_FOOTER' => 'site_footer.html.php',
        '_SIZE' => 'size',
        '_SUBJECT' => 'subject',
        '_TERA_WORKER_COUNT' => 'terasender_worker_count',
        '_TO' => 'to',
        '_YES' => 'yes',
        
        '_SITE_SPLASHHEAD' => 'site_splash.html.php@1',
        '_SITE_SPLASHTEXT' => 'site_splash.html.php@2',
        
        '_HELP_TEXT' => 'help_text.html.php',
        '_ABOUT_TEXT' => 'about_text.html.php',
        '_AUPTERMS' => 'aupterms.html.php',
    );
    
    $files = array();
    foreach(Lang::getAvailableLanguages() as $dfn) {
        $file = FILESENDER_BASE.'/config/'.$dfn['path'].'.php';
        if(!file_exists($file)) continue;
        $files[$dfn['path']] = $file;
    }
    
    if(!count($files)) return;
    
    $dir = FILESENDER_BASE.'/config/language/';
    if(!is_dir($dir) && !mkdir($dir))
        throw new Exception('Could not create '.$dir);
    
    foreach($files as $code => $original_file) {
        $path = $dir.$code.'/';
        if(!is_dir($path) && !mkdir($path))
            throw new Exception('Could not create '.$path);
        
        $lang = array();
        include $original_file;
        
        $create = array();
        
        $lang_file = $path.'lang.php';
        if($fh = fopen($lang_file, 'w')) {
            fwrite($fh, '<?php'."\n\n");
            
            foreach($lang as $id => $string) {
                if(array_key_exists($id, $renamed)) {
                    if(preg_match('`\.(?:html|text)\.php(?:@([0-9]+))?$`', $renamed[$id], $m)) {
                        if(!array_key_exists($renamed[$id], $create))
                            $create[$renamed[$id]] = array();
                        
                        $p = (count($m) > 1) ? (int)$m[1] : 0;
                        $create[$renamed[$id]]['o'.sprintf('%03d', $p)] = $string;
                        continue;
                        
                    } else $id = $renamed[$id];
                }
                
                fwrite($fh, '$lang[\''.$id.'\'] = \''.str_replace('\'', '\\\'', $string).'\''."\n");
            }
            
            fclose($fh);
        } else throw new CoreCannotWriteFileException($path.'lang.php');
        
        foreach($create as $file => $strings) {
            ksort($strings);
            
            if($fh = fopen($path.$file, 'w')) {
                fwrite($fh, implode("\n", $strings));
                fclose($fh);
            } else throw new CoreCannotWriteFileException($path.$file);
        }
    }
});

// Rename config parameters
Upgrader::register('post', function() use($uid) {
    $renamed = array();
    
    $config_file = FILESENDER_BASE.'/config/config.php';
    $config = file_get_contents($config_file);
    
    foreach($renamed as $old_name => $new_name)
        $config = preg_replace('`\$config\[[\'"]'.$old_name.'[\'"]\]`', '$config[\''.$new_name.'\']', $config);
    
    // Backup old file
    if(!copy($config_file, str_replace('config.php', 'config.'.date('YmdHis').'.before_rename.php', $config_file)))
        throw new Exception('Couldn\'t backup config file');
    
    // Save to file
    if($fh = fopen($config_file, 'w')) {
        fwrite($fh, $config);
        fclose($fh);
        
    } else throw new CoreCannotWriteFileException($config_file);
});
