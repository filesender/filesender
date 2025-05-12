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
 * Class containing upload options for guest voucher
 */

class GuestOptions extends Enum
{
    const EMAIL_UPLOAD_STARTED          = 'email_upload_started';
    const EMAIL_UPLOAD_PAGE_ACCESS      = 'email_upload_page_access';
    const VALID_ONLY_ONE_TIME           = 'valid_only_one_time';
    const DOES_NOT_EXPIRE               = 'does_not_expire';
    const CAN_ONLY_SEND_TO_ME           = 'can_only_send_to_me';
    const EMAIL_GUEST_CREATED           = 'email_guest_created';
    const EMAIL_GUEST_CREATED_RECEIPT   = 'email_guest_created_receipt';
    const EMAIL_GUEST_EXPIRED           = 'email_guest_expired';
    const GUEST_UPLOAD_DEFAULT_EXPIRE_IS_GUEST_EXPIRE = 'guest_upload_default_expire_is_guest_expire';
    const GUEST_UPLOAD_EXPIRE_READ_ONLY = 'guest_upload_expire_read_only';
    const OPENPGP_ENCRYPT_PASSPHRASE_TO_EMAIL = 'openpgp_encrypt_passphrase_to_email';
}
