<?php

/**
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
class AuthGuest
{
    /**
     * Cache authentication status
     */
    private static $isAuthenticated = null;
    
    /**
     * Cache guest
     */
    private static $guest = null;
    
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
        if (is_null(self::$isAuthenticated)) {
            self::$isAuthenticated = false;

            // Do we have a valid guest token in the query ?
            // This can not be forced because this method can be called for a
            // regular user to see if they are a guest. The stronger check is
            // done in getGuest() which makes sure there is a vid too
            // normal code should do something like the below to ensure they
            // are talking to a guest and that the guest has a vid provided.
            //
            //  if (Auth::isGuest()) {
            //     $guest = AuthGuest::getGuest();
            //  ...
            //
            if (array_key_exists('vid', $_REQUEST)) {
                $vid = $_REQUEST['vid'];

                // stop here if the vid is invalid
                if ( !Utilities::isValidUID($vid)) {
                    throw new TokenHasBadFormatException($vid);
                }
                
                $guest = Guest::fromToken($vid);
                    
                if ($guest->status != GuestStatuses::AVAILABLE || $guest->isExpired()) {
                    throw new GuestExpiredException($guest);
                }
                    
                self::$isAuthenticated = true;
                self::$guest = $guest;
                
                // Update last guest activity
                self::$guest->last_activity = time();
                self::$guest->save();
            }
        }
        
        return self::$isAuthenticated;
    }
    
    /**
     * Retreive user attributes.
     *
     * @retrun array
     */
    public static function attributes()
    {
        if (is_null(self::$attributes)) {
            if (!self::isAuthenticated()) {
                throw new AuthAuthenticationNotFoundException();
            }
            
            self::$attributes = array(
                'uid' => self::$guest->saml_user_identification_uid,
                'email' => self::$guest->owner->email,
                'name' => null,
                'guest' => self::$guest
            );
        }
        
        return self::$attributes;
    }
    
    /**
     * Retreive guest. This method requires a 'vid' is present in the request and 
     * that vid has a matching guest entry in the database.
     *
     * @return Guest object
     */
    public static function getGuest()
    {
        if (!array_key_exists('vid', $_REQUEST)) {
            throw new TokenHasBadFormatException('');
        }

        // we know there is a vid to lookup so from here we either get a guest
        // or we will throw.
        return self::isAuthenticated() ? self::$guest : null;
    }
}
