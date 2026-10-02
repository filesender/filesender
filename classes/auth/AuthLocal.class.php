<?php

/**
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * Guest authentication class
 *
 * Handles guest authentication.
 */
class AuthLocal
{
    /**
     * Cache attributes
     */
    private static $attributes = null;
    
    /**
     * Authentication check.
     *
     * @return bool
     */
    public static function isAuthenticated()
    {
        return !is_null(self::$attributes);
    }
    
    /**
     * Retreive user attributes.
     *
     * @retrun array
     */
    public static function attributes()
    {
        if (is_null(self::$attributes)) {
            throw new AuthAuthenticationNotFoundException();
        }
        
        return self::$attributes;
    }
    
    /**
     * Set local user
     *
     * @param string $user_id user id
     * @param string $email user email
     * @param string $name user name
     *
     */
    public static function setUser($user_id, $email, $name = null)
    {
        if (is_null($user_id)) { // Virtually closes the local session
            self::$attributes = null;
        } else {
            self::$attributes = array(
                'uid' => $user_id,
                'email' => $email,
                'name' => $name
            );
            /* $authid = Authentication::ensure('filesender-authlocal@localhost.localdomain', 'auth local')->id;
             * $user = User::fromAuthId( $authid );
             * $user->email_addresses = 'filesender-authlocal@localhost.localdomain';
             */
            $authid = Authentication::ensure($user_id, 'auth local')->id;
            $user = User::fromAuthId($authid);
            $user->email_addresses = $email;
            $user->name = $name;
            
            Auth::setUserObject($user);
        }
    }
}
