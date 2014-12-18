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

Logger::info('Cron cleanup task started');

// Close expired transfers
foreach(Transfer::allExpired() as $transfer)
    $transfer->close();

// Close expired guests
foreach(Guest::allExpired() as $guest)
    $guest->close();

// Delete expired audit logs and related data
foreach(Transfer::allExpiredAuditlogs() as $transfer) {
    AuditLog::clean($transfer);
    $transfer->delete();
}

Logger::info('Cron cleanup complete');



Logger::info('Guest accesses reporting started');

foreach(Guest::allAvailable() as $guest) {
    if(!$guest->hasOption(GuestOptions::EMAIL_GUEST_ACCESS_UPLOAD_PAGE)) continue;
    
    if(!$guest->last_activity || $guest->last_activity < strtotime('yesterday')) continue;
    
    // Send mail to guest the owner of the voucher
    ApplicationMail::quickSend('guest_access_upload_page', $guest->user_email, $guest);
}

Logger::info('Guest accesses reporting complete');



$report = Config::get('report_bounces');
if(in_array($report, array('daily', 'asap_then_daily'))) {
    Logger::info('Bounces reporting started');
    
    foreach(TrackingEvent::getNonReported(TrackingEventTypes::BOUNCE) as $set)
        TrackingEvent::reportSet($set);
    
    Logger::info('Bounces reporting complete');
}
