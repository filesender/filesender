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
 * Class containing upload options
 */

class TransferOptions extends Enum
{
    const EMAIL_ME_COPIES                           = 'email_me_copies';
    const EMAIL_ME_ON_EXPIRE                        = 'email_me_on_expire';
    const EMAIL_UPLOAD_COMPLETE                     = 'email_upload_complete';
    const EMAIL_DOWNLOAD_COMPLETE                   = 'email_download_complete';
    const EMAIL_DAILY_STATISTICS                    = 'email_daily_statistics';
    const EMAIL_REPORT_ON_CLOSING                   = 'email_report_on_closing';
    const ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE  = 'enable_recipient_email_download_complete';
    const ADD_ME_TO_RECIPIENTS                      = 'add_me_to_recipients';
    const EMAIL_RECIPIENT_WHEN_TRANSFER_EXPIRES     = 'email_recipient_when_transfer_expires';
    
    const GET_A_LINK                                = 'get_a_link';
    
    const HIDE_SENDER_EMAIL                         = 'hide_sender_email';
    
    const REDIRECT_URL_ON_COMPLETE                  = 'redirect_url_on_complete';

    const ENCRYPTION                                = 'encryption';
    const COLLECTION                                = 'collection';
    const MUST_BE_LOGGED_IN_TO_DOWNLOAD             = 'must_be_logged_in_to_download';

    // Optional options specific to S3 storage
    const STORAGE_CLOUD_S3_BUCKET                   = 'storage_cloud_s3_bucket';
    
    const WEB_NOTIFICATION_WHEN_UPLOAD_IS_COMPLETE  = 'web_notification_when_upload_is_complete';

    const VERIFY_EMAIL_TO_DOWNLOAD                  = 'verify_email_to_download';
    
}
