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
 * Fake service provider authentication class
 *
 * Handles fake service provider authentication.
 */
class AuthSPFake
{
    /**
     * Cache config
     */
    private static $config = null;
    
    /**
     * Cache authentication status
     */
    private static $isAuthenticated = null;
    
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
            self::$isAuthenticated = Config::get('auth_sp_fake_authenticated');
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
                throw new AuthSPAuthenticationNotFoundException();
            }
            
            $attributes = array();
            
            // Wanted attributes
            foreach (array('uid', 'name', 'email') as $attr) {
                // Keys in raw_attributes (can be array of key)
                $attributes[$attr] = Config::get('auth_sp_fake_'.$attr);
            }
            
            // Check attributes
            if (!$attributes['uid']) {
                throw new AuthSPMissingAttributeException(
                    'uid',
                    $attributes,
                    'uid',
                    'uid'
                );
            }
            
            if (!$attributes['email']) {
                throw new AuthSPMissingAttributeException(
                     'email',
                    $attributes,
                    'email',
                    'email'
                );
            }
            
            if (!is_array($attributes['email'])) {
                $attributes['email'] = array($attributes['email']);
            }
            
            foreach ($attributes['email'] as $email) {
                if (!Utilities::validateEmail($email)) {
                    throw new AuthSPBadAttributeException('email');
                }
            }
            
//             if (!$attributes['name']) {
//                 $attributes['name'] = substr($attributes['email'], 0, strpos($attributes['email'], '@'));
//             }
            
            // Build additional attributes
            $additional_attributes = Config::get('auth_sp_additional_attributes');
            if ($additional_attributes) {
                $additional_attributes_values = (array)Config::get('auth_sp_fake_additional_attributes_values');
                $attributes['additional'] = array();
                foreach ($additional_attributes as $key => $from) {
                    if (is_numeric($key) && is_callable($from)) {
                        continue;
                    }
                    
                    if (is_callable($from)) {
                        $value = $from();
                    } elseif (array_key_exists($from, $additional_attributes_values)) {
                        $value = $additional_attributes_values[$from];
                    } else {
                        $value = null;
                    }
                    
                    $attributes['additional'][is_numeric($key) ? $from : $key] = $value;
                }
            }
            
            self::$attributes = $attributes;
        }
        
        return self::$attributes;
    }
    
    /**
     * Generate the logon URL.
     *
     * @param $target
     *
     * @retrun string
     */
    public static function logonURL($target = null)
    {
        if (!$target) {
            $landing_page = Config::get('landing_page');
            if (!$landing_page) {
                $landing_page = 'upload';
            }
            $target = Utilities::http_build_query(array('s' => $landing_page));
        }
        
        return Config::get('site_url').'#logon-'.urlencode($target);
    }
    
    /**
     * Generate the logoff URL.
     *
     * @param $target
     *
     * @retrun string
     */
    public static function logoffURL($target = null)
    {
        if (!$target) {
            $target = Config::get('site_logouturl');
        }
        
        return Config::get('site_url').'#logoff-'.urlencode($target);
    }
}
