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
if (!defined('FILESENDER_BASE'))
    die('Missing environment');

/**
 * Guest voucher authentication class
 * 
 * Handles guest voucher authentication.
 */
class AuthVoucher {
    /**
     * Cache authentication status
     */
    private static $isAuthenticated = null;
    
    /**
     * Cache guest voucher
     */
    private static $guest_voucher = null;
    
    /**
     * Cache attributes
     */
    private static $attributes = null;
    
    /**
     * Authentication check.
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        if(is_null(self::$isAuthenticated)) {
            self::$isAuthenticated = false;
            
            if(array_key_exists('vid', $_REQUEST))  {
                $vid = $_REQUEST['vid'];
                
                if(
                    preg_match(Config::get('voucherRegEx'), $vid)
                    && strlen($vid) == Config::get('voucherUIDLength')
                ) {
                    try {
                        self::$guest_voucher = GuestVoucher::fromToken($vid);
                        self::$isAuthenticated = true;
                    } catch(GuestVoucherNotFoundException $e) {}
                }
            }
        }
        
        return self::$isAuthenticated;
    }
    
    /**
     * Retreive user attributes.
     * 
     * @retrun array
     */
    public static function attributes() {
        if(is_null(self::$attributes)) {
            if(!self::authenticated()) throw new AuthAuthenticationNotFoundException();
            
            self::$attributes = array(
                'uid' => self::$guest_voucher->user_id,
                'email' => null,
                'name' => null,
                'voucher' => self::$guest_voucher
            );
        }
        
        return self::$attributes;
    }
    
    /**
     * Retreive guest voucher
     * 
     * @return GuestVoucher object
     */
    public static function getVoucher() {
        return self::isAuthenticated() ? self::$guest_voucher : null;
    }
}
