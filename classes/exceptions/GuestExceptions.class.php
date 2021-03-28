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
 * *    Redistributions of source code must retain the above copynright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

if (!defined('FILESENDER_BASE')) {        // Require environment (fatal)
    die('Missing environment');
}

/**
 * Unknown guest exception
 */
class GuestNotFoundException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector column used to select guest
     */
    public function __construct($selector)
    {
        parent::__construct(
            'guest_not_found', // Message to give to the user
            array('selector' => $selector) // Real message to log
        );
    }
}

/**
 * We filter out URLs in the personal message
 */
class GuestMessageBodyCanNotIncludeURLsException extends DetailedException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct()
    {
        parent::__construct('message_can_not_contain_urls');
    }
}

/**
 * Bad status exception
 */
class GuestBadStatusException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $status the bad status
     */
    public function __construct($status)
    {
        parent::__construct(
            'bad_guest_status', // Message to give to the user
            array('status' => $status) // Details to log
        );
    }
}

/**
 * Hit the limit of reminders
 */
class GuestReminderLimitReachedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $status the bad status
     */
    public function __construct()
    {
        parent::__construct(
            'guest_reminder_limit_reached'
        );
    }
}
class GuestReminderRateLimitReachedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $status the bad status
     */
    public function __construct()
    {
        parent::__construct(
            'guest_reminder_rate_limit_reached'
        );
    }
}

/**
 * Generic identified guest exception
 */
class GuestException extends DetailedException
{
    /**
     * Constructor
     *
     * @param Guest $guest
     * @param string $msg_code message code to be used to present error
     * @param mixed $internal_details details to log
     * @param mixed $public_details details to give to the user (logged as well)
     */
    public function __construct($guest, $msg_code, $internal_details = null, $public_details = null)
    {
        $internal_details = $internal_details ? (array)$internal_details : array();
        $internal_details['guest'] = (string)$guest;
        
        parent::__construct('guest_'.$msg_code, $internal_details, $public_details);
    }
}

/**
 * Expired
 */
class GuestExpiredException extends GuestException
{
    /**
     * Constructor
     *
     * @param Guest $guest
     */
    public function __construct($guest)
    {
        parent::__construct($guest, 'expired');
    }
}

/**
 * Expiry extension not allowed
 */
class GuestExpiryExtensionNotAllowedException extends GuestException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'expiry_extension_not_allowed');
    }
}

/**
 * Expiry extension count exceeded
 */
class GuestExpiryExtensionCountExceededException extends GuestException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'expiry_extension_count_exceeded');
    }
}
