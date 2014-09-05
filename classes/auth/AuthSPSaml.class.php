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
 * SAML service provider authentication class
 * 
 * Handles SAML service provider authentication.
 */
class AuthSPSaml {
    /**
     * Cache config
     */
    private static $config = null;
    
    /**
     * Cache SimpleSamlPHP_Auth_Simple instance
     */
    private static $simplesamlphp_auth_simple = null;
    
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
    public static function isAuthenticated() {
        if(is_null(self::$isAuthenticated)) self::$isAuthenticated = self::loadSimpleSAML()->isAuthenticated();
        
        return self::$isAuthenticated;
    }
    
    /**
     * Retreive user attributes.
     * 
     * @retrun array
     */
    public static function attributes() {
        if(is_null(self::$attributes)) {
            if(!self::isAuthenticated()) throw new AuthSPAuthenticationNotFoundException();
            
            $raw_attributes = self::loadSimpleSAML()->getAttributes();
            
            $attributes = array();
            
            // Wanted attributes
            foreach(array('uid', 'name', 'email') as $attr) {
                // Keys in raw_attributes (can be array of key)
                $keys = self::$config[$attr.'_attribute'];
                if(!is_array($keys)) $keys = array($keys);
                
                $values = array();
                foreach($keys as $key) { // For all possible keys for attribute
                    $value = $raw_attributes[$key];
                    if(!is_array($value)) $value = array($value);
                    foreach($value as $v) $values[] = $v; // Gather values of all successive possible keys as array
                }
                $values = array_filter(array_map('trim', $values)); // Remove empty values
                
                $attributes[$attr] = count($values) ? $values : null;
            }
            
            if(is_array($attributes['uid'])) $attributes['uid'] = array_shift($attributes['uid']);
            if(is_array($attributes['name'])) $attributes['name'] = array_shift($attributes['name']);
            
            if(!$attributes['uid']) throw new AuthSPMissingAttributeException('uid');
            
            if(!$attributes['email']) throw new AuthSPMissingAttributeException('email');
            
            foreach($attributes['email'] as $email) {
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new AuthSPBadAttributeException('email');
            }
            
            if(!$attributes['name']) $attributes['name'] = substr($attributes['email'][0], 0, strpos($attributes['email'][0], '@'));
            
            self::$attributes = $attributes;
        }
        
        return self::$attributes;
    }
    
    /**
     * Generate the logon URL.
     * 
     * @retrun string
     */
    public static function logonURL() {
        self::loadSimpleSAML();
        
        $url = self::$config['simplesamlphp_url'].'module.php/core/as_login.php?';
        $url .= 'AuthId='.self::$config['authentication_source'];
        $url .= '&ReturnTo='.Config::get('site_url').'index.php?s=upload';
        
        return $url;
    }
    
    /**
     * Generate the logoff URL.
     * 
     * @retrun string
     */
    public static function logoffURL() {
        self::loadSimpleSAML();
        
        $url = self::$config['simplesamlphp_url'].'module.php/core/as_logout.php?';
        $url .= 'AuthId='.self::$config['authentication_source'];
        $url .= '&ReturnTo='.Config::get('site_logouturl');
        
        return $url;
    }
    
    /**
     * Load SimpleSAML class
     */
    private static function loadSimpleSAML() {
        if(is_null(self::$config)) {
            self::$config = Config::get('auth_sp_saml_*');
            
            foreach(array(
                'simplesamlphp_location',
                'simplesamlphp_url',
                'authentication_source',
                'uid_attribute',
                'name_attribute',
                'email_attribute'
            ) as $key) if(!array_key_exists($key, self::$config))
                throw new ConfigMissingParameterException('auth_sp_saml_'.$key);
        }
        
        if(is_null(self::$simplesamlphp_auth_simple)) {
            require_once(self::$config['simplesamlphp_location'] . 'lib/_autoload.php');
            
            self::$simplesamlphp_auth_simple = new SimpleSAML_Auth_Simple(self::$config['authentication_source']);
        }
        
        return self::$simplesamlphp_auth_simple;
    }
}
