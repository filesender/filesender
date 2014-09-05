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
 * Class containing transfer statuses
 */

class UploadOptions extends Enum {
    const EMAIL_ME_COPIES_AVAILABLE             = 'email_me_copies_available';
    const EMAIL_ME_COPIES_DEFAULT               = 'email_me_copies_default';
    const EMAIL_ME_COPIES_ADVANCED              = 'email_me_copies_advanced';
    
    const UPLOAD_COMPLETE_EMAIL_DISPLAY         = 'upload_complete_email_display';
    const UPLOAD_COMPLETE_EMAIL_DEFAULT         = 'upload_complete_email_default';
    
    const INFORM_DOWNLOAD_EMAIL_DISPLAY         = 'inform_download_email_display';
    const INFORM_DOWNLOAD_EMAIL_DEFAULT         = 'inform_download_email_default';
    
    const EMAIL_ME_DAILY_STATISTICS_DISPLAY     = 'email_me_daily_statistics_display';
    const EMAIL_ME_DAILY_STATISTICS_DEFAULT     = 'email_me_daily_statistics_default';
    
    const DOWNLOAD_CONFIRMATION_ENABLED_DISPLAY = 'download_confirmation_enabled_display';
    const DOWNLOAD_CONFIRMATION_ENABLED_DEFAULT = 'download_confirmation_enabled_default';
    
    const ADD_ME_TO_RECIPIENTS_DISPLAY          = 'add_me_to_recipients_display';
    const ADD_ME_TO_RECIPIENTS_DEFAULT          = 'add_me_to_recipients_default';
}

