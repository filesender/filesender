<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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

// Actions BEFORE database structure upgrade
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

// Actions AFTER database structure upgrade
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
