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
   const UPLOAD                 = 'UPLOAD';
   const FAILED                 = 'FAILED';
   const DOWNLOAD               = 'DOWNLOAD';
   const UPLOADED               = 'UPLOADED';
   const LOG_CREATED            = 'LOG_CREATED';
   
   
   /* USER */
   const USER_ACTIVATED         = 'USER_ACTIVATED';
   const USER_INACTIVE          = 'USER_INACTIVE';
   const USER_PURGED            = 'USER_PURGED';
   
   /* FILE */
   const FILE_UPDATED           = 'FILE_UPDATED';
   const FILE_EXPIRED           = 'FILE_EXPIRED';
   const FILE_MOVED             = 'FILE_MOVED';
   const FILE_DELETED           = 'FILE_DELETED';
   
   /* GUESTVOUCHER */
   const GUESTVOUCHER_CREATED   = 'GUESTVOUCHER_CREATED';
   const GUESTVOUCHER_SENT      = 'GUESTVOUCHER_SENT';
   const GUESTVOUCHER_USED      = 'GUESTVOUCHER_USED';
   const GUESTVOUCHER_EXPIRED   = 'GUESTVOUCHER_EXPIRED';
   const GUESTVOUCHER_CANCEL    = 'GUESTVOUCHER_CANCEL';
   const GUESTVOUCHER_CLOSED    = 'GUESTVOUCHER_CLOSED';
   
   /* TRANSFER */
   const TRANSFER_START         = 'TRANSFER_START';
   const TRANSFER_EXPIRED       = 'TRANSFER_EXPIRED';
   const TRANSFER_CLOSED        = 'TRANSFER_CLOSED';
   const TRANSFER_DELETED       = 'TRANSFER_DELETED';
   
   /* UPLOAD */
   const UPLOAD_START           = 'UPLOAD_START';
   const UPLOAD_END             = 'UPLOAD_END';
   
   /* DOWNLOAD */
   const DOWNLOAD_START         = 'DOWNLOAD_START';
   const DOWNLOAD_END           = 'DOWNLOAD_END';
   const DOWNLOAD_RESUME        = 'DOWNLOAD_RESUME';
   
}