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

require_once(dirname(__FILE__).'/../../includes/init.php');

Logger::setProcess(ProcessTypes::CRON);
Logger::info('Cron started');

$testingMode = (count($argv) > 1) ? $argv[1]=='--testing-mode' : false;
if( $testingMode ) {
    Mail::TESTING_SET_DO_NOT_SEND_EMAIL();
}

// Log some daily statistics first
$storage_usage = Storage::getUsage();
if(!is_null($storage_usage)) {
    $used = 0;
    foreach($storage_usage as $info) {
        $used += $info['total_space'] - $info['free_space'];
    }
    StatLog::createGlobal(LogEventTypes::GLOBAL_STORAGE_USAGE, $used);
}

StatLog::createGlobal(LogEventTypes::GLOBAL_ACTIVE_USERS, count(User::getActive()));

StatLog::createGlobal(LogEventTypes::GLOBAL_AVAILABLE_TRANSFERS, count(Transfer::all(Transfer::AVAILABLE)));

// Close expired transfers
foreach(Transfer::allExpired() as $transfer) {
    if($transfer->status == TransferStatuses::CLOSED) {
        continue;
    }
    Logger::info($transfer.' expired, closing it');
    $transfer->close(false);
}

// Delete failed transfers
foreach(Transfer::allFailed() as $transfer) {
    Logger::info($transfer.' failed, deleting it');
    $transfer->delete();
}

// Close expired guests
$days = Config::get('guests_expired_lifetime');
foreach(Guest::allExpired() as $guest) {
    if($guest->getOption(GuestOptions::DOES_NOT_EXPIRE)) continue;

    if( $days != -1 && $guest->isExpiredDaysAgo($days)) {
        Logger::info($guest.' expired and before guests_expired_lifetime so deleting it');
        $guest->delete();
    } else {
        if($guest->status == GuestStatuses::CLOSED) continue;
        Logger::info($guest.' expired, closing it');
        $guest->close(false);
    }
}

// Delete expired audit logs and related data
foreach(Transfer::allExpiredAuditlogs() as $transfer) {
    Logger::info($transfer.' auditlogs expired, deleting them and deleting transfer data');
    AuditLog::clean($transfer);
    $transfer->delete();
}

// Send daily summaries
foreach(Transfer::all(Transfer::AVAILABLE) as $transfer) {
    if(!$transfer->getOption(TransferOptions::EMAIL_DAILY_STATISTICS)) continue;
    
    Logger::info('Sending daily report for '.$transfer);
    
    $start_time = time() - 24 * 3600;
    $events = array();
    
    foreach($transfer->auditlogs as $log) {
        if($log->created < $start_time) continue;
        if($log->author_type != 'Recipient') continue;
        if(!in_array($log->event, array(LogEventTypes::DOWNLOAD_ENDED, LogEventTypes::ARCHIVE_DOWNLOAD_ENDED))) continue;
        
        $events[] = array(
            'who' => $log->author->email,
            'what' => ($log->event == LogEventTypes::ARCHIVE_DOWNLOAD_ENDED) ? 'archive' : 'file',
            'what_name' => ($log->event == LogEventTypes::ARCHIVE_DOWNLOAD_ENDED) ? '' : $log->target->name,
            'when' => $log->created
        );
    }
    
    ApplicationMail::quickSend('daily_summary', $transfer->owner, $transfer, array('events' => $events));
}

// Send automatic reminders
if(Config::get('transfer_automatic_reminder'))
    Transfer::sendAutomaticReminders();

// Report bounces ?
$report = Config::get('report_bounces');
if(in_array($report, array('daily', 'asap_then_daily'))) {
    Logger::info('Bounces reporting in effect, gathering bounces and reporting them');
    
    foreach(TrackingEvent::getNonReported(TrackingEventTypes::BOUNCE) as $set) {
        TrackingEvent::reportSet($set);
    }
}

// Storage warning ?
$level = Config::get('storage_usage_warning');
if((int)$level) {
    $usage = Storage::getUsage();
    
    $block_warnings = array();
    if($usage) foreach($usage as $fs => $u) {
        if($u['free_space'] > $level * $u['total_space'] / 100) continue;
        $u['free_space_pct'] = floor(100 * $u['free_space'] / $u['total_space']);
        $u['filesystem'] = $fs;
        $block_warnings[] = $u;
    }
    
    if(count($block_warnings)) {
        Logger::info('Storage is warning, reporting');
        SystemMail::quickSend('storage_usage_warning', array('warnings' => $block_warnings));
    }
}

// Remove inactive users preferences
User::removeInactive();

// Clean old client logs
ClientLog::clean();

// Clean old translated emails
TranslatableEmail::clean();

// Clean old tracking events 
TrackingEvent::clean();

// Clean old tracking events 
StatLog::clean();

// Clean old auditlog events
AuditLog::cleanup();

