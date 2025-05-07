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

//
// False by default, if present it is set
//
function getBoolArg( $name )
{
    global $argv;
    
    $ret = (count($argv) > 1) ? $argv[1]==$name : false;
    if( !$ret && count($argv) > 2 ) {
        $ret = ($argv[2]==$name);
        if( !$ret && count($argv) > 3 ) {
            $ret = ($argv[3]==$name);
        }
    }
    return $ret;
}

//
// Print some messages to give a hint to the user on progress
//
$verbose = getBoolArg('--verbose');

//
// If one or more files in the transfer can not be deleted
// then the error should be logged and the transfer delete
// should complete rather than throwing an exception and
// stopping. This might be handy if the sys admin has already
// deleted some files and the system is halting when it tries
// to delete those same files.
//
$force = getBoolArg('--force');

//
// Mainly a developer feature. Do not send emails to allow rapid testing
//
$testingMode = getBoolArg('--testing-mode'); // (count($argv) > 1) ? $argv[1]=='--testing-mode' : false;
if( $testingMode ) {
    Mail::TESTING_SET_DO_NOT_SEND_EMAIL();
}





if( $verbose ) {
    echo "cron.php starting up... --force:$force --testing-mode:$testingMode\n";
    echo "cron.php running as user: " . `id` . "\n";
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
if( $verbose ) echo "cron.php closing expired transfers...\n";
foreach(Transfer::allExpired() as $transfer) {
    if($transfer->status == TransferStatuses::CLOSED) {
        continue;
    }
    Logger::info($transfer.' expired, closing it');
    $transfer->close(false, $force );
}

// Delete failed transfers
if( $verbose ) echo "cron.php delete failed transfers...\n";
foreach(Transfer::allFailed() as $transfer) {
    Logger::info($transfer.' failed, deleting it');
    if( $force ) {
        $transfer->deleteForce = true;
    }
    $transfer->delete();
}

// Close expired guests
if( $verbose ) echo "cron.php close expired guests...\n";
$days = Config::get('guests_expired_lifetime');
foreach(Guest::allExpired() as $guest) {
    if($guest->does_not_expire) continue;

    if( $days != -1
     && $guest->isClosed()
     && $guest->isExpiredDaysAgo($days))
    {
        //
        // only delete the guest if there are no available transfers
        // created by that guest.
        $transfers = Transfer::fromGuest($guest);
        if( !count($transfers)) {
            Logger::info($guest.' expired and before guests_expired_lifetime so deleting it');
            $guest->delete();
        }
    }
    else
    {
        if($guest->isClosed()) continue;
        Logger::info($guest.' expired, closing it');
        $guest->close(false);
    }
}

// Delete expired audit logs and related data
if( $verbose ) echo "cron.php Delete expired audit logs and related data...\n";
foreach(Transfer::allExpiredAuditlogs() as $transfer) {
    Logger::info($transfer.' auditlogs expired, deleting them and deleting transfer data');
    AuditLog::clean($transfer);
    $transfer->deleteForce = $force;
    $transfer->delete();
}

// Send daily summaries
if( $verbose ) echo "cron.php Send daily summaries...\n";
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
if( $verbose ) echo "cron.php Send automatic reminders...\n";
if(Config::get('transfer_automatic_reminder'))
    Transfer::sendAutomaticReminders();

// Report bounces ?
if( $verbose ) echo "cron.php Report bounces ?...\n";
$report = Config::get('report_bounces');
if(in_array($report, array('daily', 'asap_then_daily'))) {
    Logger::info('Bounces reporting in effect, gathering bounces and reporting them');
    
    foreach(TrackingEvent::getNonReported(TrackingEventTypes::BOUNCE) as $set) {
        TrackingEvent::reportSet($set);
    }
}

// Storage warning ?
if( $verbose ) echo "cron.php Storage warning ?...\n";
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
if( $verbose ) echo "cron.php Remove inactive users...\n";
User::removeInactive();

// Clean old client logs
if( $verbose ) echo "cron.php Clean up old client logs...\n";
ClientLog::clean();

// Clean old translated emails
if( $verbose ) echo "cron.php Clean old translated emails...\n";
TranslatableEmail::clean();

// Clean old tracking events 
if( $verbose ) echo "cron.php Clean old tracking events...\n";
TrackingEvent::clean();

// Clean old tracking events 
if( $verbose ) echo "cron.php Clean old tracking events (statlog)...\n";
StatLog::clean();

// Clean old auditlog events
if( $verbose ) echo "cron.php Clean old auditlog events...\n";
AuditLog::cleanup();

// Clean old ratelimithistory events
if( $verbose ) echo "cron.php Clean old ratelimithistory events...\n";
RateLimitHistory::cleanup();

// Clean up S3 buckets if storage backend is set to CloudS3 and configuration
// option cloud_s3_use_daily_bucket has been set to true
if (Utilities::startsWith(strtolower(Config::get('storage_type')), 'clouds3') && Config::get('cloud_s3_use_daily_bucket')) {
    if( $verbose ) echo "cron.php S3 daily bucket maintenance...\n";
    StorageCloudS3::dailyBucketMaintenance($verbose);
}

if( Config::get("download_verification_code_enabled")) {
    DownloadOneTimePassword::cleanup();
}

StorageFilesystem::deleteEmptyBucketDirectories();

// If we are configured to send aggregate (anonymous) statistics
// to a central server then we should check if it is time to do that.
AggregateStatistic::maybeSendReport();
