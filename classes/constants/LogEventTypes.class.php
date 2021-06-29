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

/**
 * Class containing all tags (constants) for audit logs
 *
 * Note that the _LH entries are made by Logger::rateLimit() for the caller automatically
 * to indicate a "Limit is Hit" for an event type.
 */
class LogEventTypes extends Enum
{
    /* GENERAL */
    const LOG_CREATED              = 'log_created';    // Log created
   
    /* USER */
    const USER_CREATED             = 'user_created'; // User become active
   const USER_INACTIVED           = 'user_inactived';  // User become inactive
   const USER_PURGED              = 'user_purged';    // User purged
   
   /* FILE */
    const FILE_CREATED             = 'file_created';   // File has been updated
   const FILE_UPLOADED            = 'file_uploaded';  // File has been uploaded
   const FILE_UPDATED             = 'file_updated';   // File has been updated
   const FILE_MOVED               = 'file_moved';     // File has been moved
   const FILE_DELETED             = 'file_deleted';   // File has been deleted

   /* GUEST */
   const GUEST_CREATED            = 'guest_created';         // Guest created (logs the guest)
   const GUEST_CREATED_RATE       = 'guest_created_rate';    // Guest created (target = user) used in rate limiting
   const GUEST_CREATED_LH         = 'guest_created_lh';      // limit hit (target = user)
   const GUEST_CREATED_RATE_LH    = 'guest_created_rate_lh'; // limit hit (target = user)
   const GUEST_SENT               = 'guest_sent';      // Guest send to recipients
   const GUEST_USED               = 'guest_used';      // Guest has been used
   const GUEST_EXPIRED            = 'guest_expired';   // Guest expired
   const GUEST_CLOSED             = 'guest_closed';    // Guest closed
   const GUEST_DELETED            = 'guest_deleted';   // Guest canceled
   const GUEST_CLOSED_UNUSED      = 'guest_closed_unused'; // Guest did not upload a single file
   const GUEST_REMIND_RATE        = 'guest_remind_rate';
   const GUEST_REMIND_RATE_LH     = 'guest_remind_rate_lh';

   
   /* TRANSFER */
    const TRANSFER_STARTED         = 'transfer_started';         // Transfer started
   const TRANSFER_AVAILABLE       = 'transfer_available';     // Transfer started
   const TRANSFER_SENT            = 'transfer_sent';     // Transfer started
   const TRANSFER_EXPIRED         = 'transfer_expired';       // Transfer expired
   const TRANSFER_CLOSED          = 'transfer_closed';        // Transfer closed
   const TRANSFER_DELETED         = 'transfer_deleted';       // Transfer deleted
   const TRANSFER_DECRYPT_FAILED  = 'transfer_decrypt_failed';// Transfer decrypt failed at client
   
   /* UPLOAD */
    const UPLOAD_STARTED           = 'upload_started';   // Upload stated
   const UPLOAD_RESUMED           = 'upload_resumed';  // Upload resumed
   const UPLOAD_ENDED             = 'upload_ended';     // Upload ended
   
   /* DOWNLOAD */
    const DOWNLOAD_STARTED         = 'download_started';     // Download started
   const DOWNLOAD_RESUMED         = 'download_resumed';    // Download resumed
   const DOWNLOAD_ENDED           = 'download_ended';       // Download ended
   
   /* ARCHIVE DOWNLOAD */
    const ARCHIVE_DOWNLOAD_STARTED = 'archive_download_started';     // Download started
   const ARCHIVE_DOWNLOAD_ENDED   = 'archive_download_ended';       // Download ended
   
   /* Global */
    const GLOBAL_STORAGE_USAGE       = 'global_storage_usage';
    const GLOBAL_ACTIVE_USERS        = 'global_active_users';
    const GLOBAL_AVAILABLE_TRANSFERS = 'global_available_transfers';

    /* Testing */
    const TESTING_SIMPLELOG_ENTRY = 'testing_simplelog_entry'; // ability to log some test data
}
