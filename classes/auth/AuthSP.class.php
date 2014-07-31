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

if (!defined('FILESENDER_BASE'))        // Require environment (fatal)
    die('Missing environment');

/**
 * Service provider authentication class
 * 
 * Handles service provider authentication delegation.
 */
class AuthSP {
    /**
     * Cache if delegation class was loaded.
     */
    private static $loaded = false;
    
    /**
     * Delegates authentication check.
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        $class = self::loadDelegationClass();
        
        return call_user_func($class.'::isAuthenticated');
    }
    
    /**
     * Retreive user attributes from delegated class.
     * 
     * @retrun array
     */
    public static function attributes() {
        $class = self::loadDelegationClass();
        
        return call_user_func($class.'::attributes');
    }
    
    /**
     * Get the logon URL from delegated class.
     * 
     * @retrun string
     */
    public static function logonURL() {
        $class = self::loadDelegationClass();
        
        return call_user_func($class.'::logonURL');
    }
    
    /**
     * Get the logoff URL from delegated class.
     * 
     * @retrun string
     */
    public static function logoffURL() {
        $class = self::loadDelegationClass();
        
        return call_user_func($class.'::logoffURL');
    }
    
    /**
     * Load selected service provider delegation class and return its class name.
     * 
     * @return string delegation class name
     */
    private static function loadDelegationClass() {
        if(self::$loaded) return self::$loaded;
        
        $type = Config::get('auth_sp_type');
        
        if(!$type) throw new ConfigBadParameterException('auth_sp_type');
        $class = 'AuthSP'.ucfirst($type);
        $file = FILESENDER_BASE.'/classes/auth/'.$class.'.class.php';
        
        if(!file_exists($file)) throw new AuthSPMissingDelegationClassException($class);
        
        require_once $file;
        
        self::$loaded = $class;
        
        return $class;
    }
}
