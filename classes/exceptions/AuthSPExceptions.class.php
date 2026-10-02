<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 * Missing service provider delegation class.
 */
class AuthSPMissingDelegationClassException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $name name of the delegation class
     */
    public function __construct($name)
    {
        parent::__construct(
            'auth_sp_missing_delegation_class', // Message to give to the user
            array('class' => $name) // Details to log
        );
    }
}

/**
 * Authentication no found.
 */
class AuthSPAuthenticationNotFoundException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'auth_sp_authentication_not_found'
        );
    }
}

/**
 * Missing attribute.
 */
class AuthSPMissingAttributeException extends DetailedException
{
    /**
     * Constructor
     *
     * The $attributes array is used to furnish the server only log
     * file with the current data to allow the system admin to see
     * possible key mismatches.
     *
     * @param string $name name of the attribute
     * @param array  $attributes array of key-value where a key of $name should have been found
     */
    public function __construct($name, $attributes, $attrkey, $attrname='')
    {
        $info = array('attribute' => $name);
        
        $lid = 'auth_sp_attribute_'.$name.'_hint';
        $hint = (string)Lang::tr($lid);
        if ($hint != '{'.$lid.'}') {
            $info['hint'] = $hint;
        }

        // Give the system admin some information to help them fix things
        $internalLog = DetailedException::convertArrayToLogArray($attributes, '    ');
        $this->log('attributes', (string)Lang::tr('serverlog_auth_sp_attribute_not_found'));
        $this->log('attributes', Lang::tr('serverlog_config_directive')->r('key', $attrkey));
        $this->log('attributes', Lang::tr('serverlog_wanted_key_in_array')->r('key', $attrname));
        $this->logArray('attributes', $internalLog);

        parent::__construct('auth_sp_missing_attribute', null, $info);
    }
}

/**
 * Bad attribute.
 */
class AuthSPBadAttributeException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $name name of the attribute
     */
    public function __construct($name)
    {
        $info = array('attribute' => $name);
        
        $lid = 'auth_sp_attribute_'.$name.'_hint';
        $hint = (string)Lang::tr($lid);
        if ($hint != '{'.$lid.'}') {
            $info['hint'] = $hint;
        }
        
        parent::__construct('auth_sp_bad_attribute', null, $info);
    }
}
