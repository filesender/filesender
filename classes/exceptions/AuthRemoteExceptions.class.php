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
 * *    Redistributions of source code must retain the above copyright
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
 * Request came in too late
 */
class AuthRemoteTooLateException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $by too late by
     */
    public function __construct($by)
    {
        parent::__construct(
            'auth_remote_too_late', // Message to give to the user
            array('late_by_seconds' => $by) // Details to log
        );
    }
}

/**
 * Signature check failed
 */
class AuthRemoteSignatureCheckFailedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $signed body that was signed
     * @param string $secret secret used while signing
     * @param string $received_signature received signature
     * @param string $signature expected signature
     */
    public function __construct($signed, $secret, $received_signature, $signature)
    {
        parent::__construct(
            'auth_remote_signature_check_failed', // Message to give to the user
            array(
                'signed' => $signed,
                'secret' => $secret,
                'received_signature' => $received_signature,
                'signature' => $signature
            )
        );
    }
}

/**
 * Unknown remote application
 */
class AuthRemoteUknownApplicationException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $name name of the remote application
     */
    public function __construct($name)
    {
        parent::__construct(
            'auth_remote_unknown_application', // Message to give to the user
            array('application' => $name) // Details to log
        );
    }
}

/**
 * User does not accept remote authentication
 */
class AuthRemoteUserRejectedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $uid user id
     * @param string $reason
     */
    public function __construct($uid, $reason)
    {
        parent::__construct(
            'auth_remote_user_rejected', // Message to give to the user
            array('uid' => $uid, 'reason' => $reason) // Details to log
        );
    }
}
