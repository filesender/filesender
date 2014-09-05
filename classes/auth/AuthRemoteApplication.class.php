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
 * Remote application authentication class
 * 
 * Handles remote application authentication.
 */
class AuthRemoteApplication {
    /**
     * Cache authentication status
     */
    private static $isAuthenticated = null;
    
    /**
     * Cache admin state
     */
    private static $isAdmin = false;
    
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
            
            if(array_key_exists('remote_application', $_GET) && array_key_exists('signature', $_GET)) {
                self::$attributes = array();
                
                $application = $_GET['remote_application'];
                self::$attributes['remote_application'] = $application;
                
                $received_signature = $_GET['signature'];
                $timestamp = (int)$_GET['timestamp'];
                
                $applications = Config::get('auth_remote_applications');
                
                if(!is_array($applications) || !array_key_exists($application, $applications))
                    throw new AuthRemoteApplicationUknownApplicationException($application);
                
                $application = $applications[$application];
                
                $late = time() - $timestamp - 15;
                if($late > 0)
                    throw new AuthRemoteApplicationTooLateException($late);
                
                $method = null;
                foreach(array('X_HTTP_METHOD_OVERRIDE', 'REQUEST_METHOD') as $k) {
                    if(!array_key_exists($k, $_SERVER)) continue;
                    $method = strtolower($_SERVER[$k]);
                }
                
                $signed = $method.'&'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].(array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '');
                
                $args = $_GET;
                unset($args['signature']);
                if(count($args)) $signed .= '?'.implode('&', RestUtilities::flatten($args));
                
                $input = AuthRemoteRequest::body();
                if($input) $signed .= '&'.$input;
                
                $signature = hash_hmac('sha1', $signed, $application['secret']);
                if($received_signature != $signature)
                    throw new AuthRemoteApplicationSignatureCheckFailedException($signed, $application['secret'], $received_signature, $signature);
                
                if(array_key_exists('remote_user', $_GET)) self::$attributes['uid'] = $_GET['remote_user'];
                
                if(array_key_exists('isAdmin', $application) && $application['isAdmin']) self::$isAdmin = true;
                
                AuthRemoteRequest::application($application);
                
                self::$isAuthenticated = true;
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
        if(!self::isAuthenticated()) throw new AuthSPAuthenticationNotFoundException();
        
        return self::$attributes;
    }
    
    /**
     * Get admin state
     */
    public static function isAdmin() {
        return self::$isAdmin;
    }
}
