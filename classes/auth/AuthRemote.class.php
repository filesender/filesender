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
 * Remote application/user authentication class
 */
class AuthRemote {
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
     * Application name
     */
    private static $application = null;
    
    /**
     * Authentication check.
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        if(is_null(self::$isAuthenticated)) {
            self::$isAuthenticated = false;
            
            // Do we have remote authentication data in the request ?
            if(!array_key_exists('signature', $_GET)) return false;
            if(!array_key_exists('timestamp', $_GET)) return false;
            
            $application = array_key_exists('remote_application', $_GET) ? $_GET['remote_application'] : null;
            $uid = array_key_exists('remote_user', $_GET) ? $_GET['remote_user'] : null;
            
            if(!$application && !$uid) return false;
            
            self::$attributes = array();
            
            // Get data
            $received_signature = $_GET['signature'];
            $timestamp = (int)$_GET['timestamp'];
            
            if($application) {
                // Check that application is known
                $applications = Config::get('auth_remote_applications');
                
                if(!is_array($applications) || !array_key_exists($application, $applications))
                    throw new AuthRemoteUknownApplicationException($application);
                
                $app_cfg = $applications[$application];
            }
            
            // Check request time to avoid replays
            $late = time() - $timestamp - 15;
            if($late > 0)
                throw new AuthRemoteTooLateException($late);
            
            // Get method from headers
            $method = null;
            foreach(array('X_HTTP_METHOD_OVERRIDE', 'REQUEST_METHOD') as $k) {
                if(!array_key_exists($k, $_SERVER)) continue;
                $method = strtolower($_SERVER[$k]);
            }
            
            // Build signed data
            $signed = $method.'&'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].(array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '');
            
            $args = $_GET;
            unset($args['signature']);
            if(count($args)) $signed .= '?'.implode('&', RestUtilities::flatten($args));
            
            $input = Request::body();
            if($input) $signed .= '&'.$input;
            
            // Check signature
            if($application) {
                $secret = $app_cfg['secret'];
                
            } else {
                // Get user, fail if unknown or no user secret
                try {
                    $user = User::fromId($uid);
                } catch(UserNotFoundException $e) {
                    throw new AuthRemoteUserRejectedException($uid, 'user not found');
                }
                
                if(!$user->auth_secret)
                    throw new AuthRemoteUserRejectedException($user->id, 'no secret set');
                
                $secret = $user->auth_secret;
            }
            $algorithm = Config::get('auth_remote_signature_algorithm');
            if(!$algorithm) $algorithm = 'sha1';
            $signature = hash_hmac($algorithm, $signed, $secret);
            if($received_signature !== $signature)
                throw new AuthRemoteSignatureCheckFailedException($signed, $secret, $received_signature, $signature);
            
            // Register user id if given
            if($uid) self::$attributes['uid'] = $uid;
            
            // Register admin level if asked for and enabled
            if($application) {
                if(array_key_exists('isAdmin', $app_cfg) && $app_cfg['isAdmin']) self::$isAdmin = true;
                
                self::$application = $application;
                self::$attributes['remote_application'] = $application;
            }
            
            self::$isAuthenticated = true;
        }
        
        return self::$isAuthenticated;
    }
    
    /**
     * Retreive user attributes.
     * 
     * @return array
     */
    public static function attributes() {
        if(!self::isAuthenticated()) throw new AuthAuthenticationNotFoundException();
        
        return self::$attributes;
    }
    
    /**
     * Get admin state
     * 
     * @return bool
     */
    public static function isAdmin() {
        return self::$isAdmin;
    }
    
    /**
     * Get application name
     * 
     * @return string
     */
    public static function application() {
        return self::$application;
    }
}
