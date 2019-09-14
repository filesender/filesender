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
 * Bad email exception
 */
class BadEmailException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $email the bad email
     */
    public function __construct($email)
    {
        parent::__construct(
            'bad_email', // Message to give to the user
            array('email' => $email) // Real message to log
        );
    }
}


/**
 * Bad email exception
 */
class BadIPFormatException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $ip the bad ip
     * @param string $protocol the bad protocol
     */
    public function __construct($ip, $protocol = null)
    {
        parent::__construct(
            $protocol == null ? 'bad_ip_format' :'bad_ip_format_'.$protocol, // Message to give to the user
            array('ip' => $ip) // Real message to log
        );
    }
}


/**
 * Bad expire exception
 */
class BadExpireException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $expire the bad expire
     */
    public function __construct($expire)
    {
        parent::__construct(
            'bad_expire', // Message to give to the user
            array('expire' => $expire) // Real message to log
        );
    }
}

/**
 * Bad size format exception - used in Utilities class
 */
class BadSizeFormatException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $size the raw, badly formated size
     */
    public function __construct($size)
    {
        parent::__construct(
            'bad_size_format', // Message to give to the user
            array('size' => $size) // Details to log
        );
    }
}

/**
 * Bad lang exception
 */
class BadLangCodeException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $code the bad lang code
     */
    public function __construct($code)
    {
        parent::__construct(
            'bad_lang_code', // Message to give to the user
            array('code' => $code) // Details to log
        );
    }
}

/**
 * Bad option name exception
 */
class BadOptionNameException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name, $notetoadmin = '')
    {
        parent::__construct(
            'bad_option_name', // Message to give to the user
            array('name' => $name,'noteToAdmin' => $notetoadmin) // Details to log
        );
    }
}

/**
 * Bad URL exception
 */
class BadURLException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $url
     */
    public function __construct($url)
    {
        parent::__construct(
            'bad_url_code', // Message to give to the user
            array('url' => $url) // Details to log
        );
    }
}


/**
 * Bad authid exception
 */
class BadAuthIDException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $url
     */
    public function __construct($aid)
    {
        parent::__construct(
            'bad_url_code', // Message to give to the user
            array('aid' => $aid) // Details to log
        );
    }
}

/**
 * Bad crypto key version
 */
class BadCryptoKeyVersionException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $url
     */
    public function __construct($v)
    {
        parent::__construct(
            'bad_crypto_key_version_code', // Message to give to the user
            array('version' => $v)         // Details to log
        );
    }
}
