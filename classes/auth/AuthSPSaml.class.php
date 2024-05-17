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
 * SAML service provider authentication class
 *
 * Handles SAML service provider authentication.
 */
class AuthSPSaml
{
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
     * Cache authentication status
     */
    private static $SimpleSAMLphpVersion = null;

    /**
     * Authentication check.
     *
     * @return bool
     */
    public static function isAuthenticated()
    {
        if (is_null(self::$isAuthenticated)) {
            self::$isAuthenticated = self::loadSimpleSAML()->isAuthenticated();
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

            $ssp = self::loadSimpleSAML();
            $raw_attributes = self::loadSimpleSAML()->getAttributes();
            
            $attributes = array();

            
            // Wanted attributes
            foreach (array('uid', 'name', 'email') as $attr) {
                // Keys in raw_attributes (can be array of key)
                $keys = self::$config[$attr.'_attribute'];
                if (!is_array($keys)) {
                    $keys = array($keys);
                }
                
                $values = array();
                foreach ($keys as $key) { // For all possible keys for attribute
                    if (array_key_exists($key, $raw_attributes)) {
                        $value = $raw_attributes[$key];
                        if (!is_array($value)) {
                            $value = array($value);
                        }
                        foreach ($value as $v) {
                            $values[] = $v;
                        } // Gather values of all successive possible keys as array
                    }
                }
                $values = array_filter(array_map('trim', $values)); // Remove empty values
                
                $attributes[$attr] = count($values) ? $values : null;
            }
            
            // Proccess received attributes
            if (is_array($attributes['uid'])) {
                $attributes['uid'] = array_shift($attributes['uid']);
            }
            if (is_array($attributes['name'])) {
                $attributes['name'] = array_shift($attributes['name']);
            }
            
            if (!$attributes['uid']) {
                throw new AuthSPMissingAttributeException(
                    'uid',
                    $raw_attributes,
                    'uid_attribute',
                    self::$config['uid_attribute']
                );
            }
            
            if (!$attributes['email']) {
                throw new AuthSPMissingAttributeException(
                    'email',
                    $raw_attributes,
                    'email_attribute',
                    self::$config['email_attribute']
                );
            }
            
            foreach ($attributes['email'] as $email) {
                if (!Utilities::validateEmail($email)) {
                    throw new AuthSPBadAttributeException('email');
                }
            }
            
            if (!$attributes['name']) {
                $attributes['name'] = substr($attributes['email'][0], 0, strpos($attributes['email'][0], '@'));
            }
            
            // Gather additional attributes if required
            $additional_attributes = Config::get('auth_sp_additional_attributes');
            if ($additional_attributes) {
                $attributes['additional'] = array();
                foreach ($additional_attributes as $key => $from) {
                    if (is_numeric($key) && is_callable($from)) {
                        continue;
                    }
                    
                    if (is_callable($from) && !is_string($from)) {
                        $value = $from($raw_attributes, self::loadSimpleSAML());
                    } elseif (array_key_exists($from, $raw_attributes)) {
                        $value = $raw_attributes[$from];
                    } else {
                        $value = null;
                    }
                    
                    $attributes['additional'][is_numeric($key) ? $from : $key] = $value;
                }
            }

            //
            // Let the javascript warn the user of possible end of session time
            //
            if( Config::get('auth_warn_session_expired')) {
                if ($v = $ssp->getAuthData('Expire')) {
                    if( !headers_sent()) {
                        // Unset the PHPSESSID cookie, so that the user will get a new session ID on their next request.
                        $params = session_get_cookie_params();
                        
                        setcookie(
                            'X-FileSender-Session-Expires',
                            $v,
                            0,
                            "/",
                            Config::get('cookie_domain'),
                            false, // $params['secure'],
                            false, // $params['httponly']
                        );
                        
                    }
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
        self::loadSimpleSAML();
        
        if (!$target) {
            $landing_page = Config::get('landing_page');
            if (!$landing_page) {
                $landing_page = 'upload';
            }
            $target = Utilities::http_build_query(array('s' => $landing_page));
        }
        
        $url = Utilities::http_build_query(array(
            'AuthId' => self::$config['authentication_source'],
            'ReturnTo' => $target,
        ), self::$simplesamlphp_auth_simple->getLoginURL($target));

        return $url;
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
        self::loadSimpleSAML();
        
        if (!$target) {
            $target = Config::get('site_logouturl');
        }
        
        $url = Utilities::http_build_query(array(
            'AuthId' => self::$config['authentication_source'],
            'ReturnTo' => $target,
        ), self::$simplesamlphp_auth_simple->getLogoutURL($target));
        
        return $url;
    }
    
    /**
     * Load SimpleSAML class
     */
    private static function loadSimpleSAML()
    {
        if (is_null(self::$config)) {
            self::$config = Config::get('auth_sp_saml_*');
            
            foreach (array(
                'simplesamlphp_location',
                'simplesamlphp_url',
                'authentication_source',
                'uid_attribute',
                'name_attribute',
                'email_attribute'
            ) as $key) {
                if (!array_key_exists($key, self::$config)) {
                    throw new ConfigMissingParameterException('auth_sp_saml_'.$key);
                }
            }
        }

        Auth::$authClassLoadingCount++;
        if (is_null(self::$simplesamlphp_auth_simple)) {
            $saml_auto_load_path = self::$config['simplesamlphp_location'] . 'lib/_autoload.php';
            Utilities::include_once_or_halt($saml_auto_load_path, 'Failed to include SimpleSAMLphp');

            // WARNING: grab the version of the library that is in use.
            // WARNING: this can cause big problems
            // $samlConfig = SimpleSAML_Configuration::getInstance();
            // self::$SimpleSAMLphpVersion = $samlConfig->getVersion();
            // Logger::info('Loaded SimpleSAMLphp version is ' . self::$SimpleSAMLphpVersion);
            
            if (class_exists("\SimpleSAML\Auth\Simple")) {
                self::$simplesamlphp_auth_simple = new SimpleSAML\Auth\Simple(self::$config['authentication_source']);
            } else {
                //For old SSP
                self::$simplesamlphp_auth_simple = new SimpleSAML_Auth_Simple(self::$config['authentication_source']);
            }
        }
        
        Auth::$authClassLoadingCount--;
        return self::$simplesamlphp_auth_simple;
    }
}
