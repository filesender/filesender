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
 */
class LogEventTypes extends Enum {
    /* GENERAL */
   const LOG_CREATED            = 'log_created';    // Log created
   
   /* USER */
   const USER_ACTIVATED         = 'user_activated'; // User become active
   const USER_INACTIVE          = 'user_inactive';  // User become inactive
   const USER_PURGED            = 'user_purged';    // User purged
   
   /* FILE */
   const FILE_UPDATED           = 'file_updated';   // File has been updated
   const FILE_MOVED             = 'file_moved';     // File has been moved
   const FILE_DELETED           = 'file_deleted';   // File has been deleted
   
   /* GUESTVOUCHER */
   const GUEST_CREATED          = 'guest_created';   // Guest created
   const GUEST_SENT             = 'guest_sent';      // Guest send to recipients
   const GUEST_USED             = 'guest_used';      // Guest has been used
   const GUEST_EXPIRED          = 'guest_expired';   // Guest expired
   const GUEST_CLOSED           = 'guest_closed';    // Guest closed
   const GUEST_DELETED          = 'guest_deleted';   // Guest canceled
   
   /* TRANSFER */
   const TRANSFER_AVAILABLE     = 'transfer_available';     // Transfer started
   const TRANSFER_START         = 'transfer_start';         // Transfer started
   const TRANSFER_EXPIRED       = 'transfer_expired';       // Transfer expired
   const TRANSFER_CLOSED        = 'transfer_closed';        // Transfer closed
   const TRANSFER_DELETED       = 'transfer_deleted';       // Transfer deleted
   
   /* UPLOAD */
   const UPLOAD_START           = 'upload_start';   // Upload stated
   const UPLOAD_RESUME          = 'upload_resume';  // Upload resumed
   const UPLOAD_END             = 'upload_end';     // Upload ended
   
   /* DOWNLOAD */
   const DOWNLOAD_START         = 'download_start';     // Download started
   const DOWNLOAD_RESUME        = 'download_resume';    // Download resumed
   const DOWNLOAD_END           = 'download_end';       // Download ended
}
