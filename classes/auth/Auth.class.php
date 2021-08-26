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

if (!defined('FILESENDER_BASE')) {        // Require environment (fatal)
    die('Missing environment');
}

/**
 * Authentication class
 *
 * Handles user authentication logic.
 */
class Auth
{
    /**
     * The curent user cache.
     */
    protected static $user = null;

    /**
     * LIMITED USE: only Auth classes should call this!
     */
    public static function setUserObject($user)
    {
        self::$user = $user;
    }
    
    /**
     * Current user allowed state
     */
    private static $allowed = true;
    
    /**
     * Attributes cache.
     */
    private static $attributes = null;
    
    /**
     * Admin status of the current user.
     */
    private static $isAdmin = null;

    /**
     * If current user is authorized to view aggregate statistics.
     */
    private static $canViewAggregateStats = null;

    // cache
    private static $canViewStats = null;
    
    /**
     * Auth provider if any auth found
     */
    private static $type = null;
    
    /**
     * Last authentication exception for systematic rethrow
     */
    private static $exception = null;

    /**
     * There can be call loops when setting up classes. For example
     * if some code calls Auth::user() indirectly while the auth system
     * is being setup then that can cause troubles.
     *
     * While this is public it should be only used by classes inside
     * the auth system
     */
    public static $authClassLoadingCount = 0;

    /**
     * Ensure session_start is called if there is no session already.
     * Note that self::$type must be guest or sp for this call to do anything.
     *
     * @return void
     */
    private static function ensure_php_session()
    {
        if ( self::isSessionStarted() === false ) {
            session_start();
        }
    }
    
    
    /**
     * Return current user if it exists.
     *
     * @return User instance or false
     */
    private static function user_protected()
    {
        if (self::$exception) {
            throw self::$exception;
        }
        
        if (is_null(self::$user)) { // Not already cached
            self::$user = false;
            
            // Authentication logic with exception memory
            try {
                if (Logger::isLocalProcess()) {
                    self::$attributes = array();
                    self::$type = 'localprocess';
                } elseif (AuthGuest::isAuthenticated()) { // Guest
                    self::$attributes = AuthGuest::attributes();
                    self::$type = 'guest';
                } elseif (AuthLocal::isAuthenticated()) { // Local (script)
                    self::$attributes = AuthLocal::attributes();
                    self::$type = 'local';
                } elseif ((Config::get('auth_remote_application_enabled') || Config::get('auth_remote_user_enabled')) && AuthRemote::isAuthenticated()) { // Remote application/user
                    if (
                        (AuthRemote::application() && Config::get('auth_remote_application_enabled')) ||
                        (!AuthRemote::application() && Config::get('auth_remote_user_enabled'))
                    ) {
                        self::$attributes = AuthRemote::attributes();
                        if (AuthRemote::application() && AuthRemote::isAdmin()) {
                            self::$isAdmin = true;
                        }
                        self::$type = 'remote';
                    }
                } else {
                    
                    // Note that AuthSP may use the SESSION which might start_session before we do
                    // so we have to allow the sys admin to ensure the
                    // session before letting AuthSP do anything in isAuthenticated().

                    if( Utilities::isTrue(Config::get("auth_sp_force_session_start_first"))) {
                        self::ensure_php_session();
                    }
                    
                    if( AuthSP::isAuthenticated()) {
                        self::$attributes = AuthSP::attributes();
                        self::$type = 'sp';
                    }
                }
            } catch (Exception $e) {
                self::$exception = $e;
                throw $e;
            }

            // If no session has been made at this point, we make one ourselves.
            // Only types 'guest' and 'sp' are browsers.
            if (in_array(self::$type, array('sp', 'guest'))) {
                self::ensure_php_session();
            }
            
            if (self::$attributes && array_key_exists('uid', self::$attributes)) {
                $user_filter = Config::get('auth_user_filter');
                if ($user_filter) {
                    self::$allowed = false;
                    
                    if (is_string($user_filter)) {
                        if (preg_match('`^([^:]+):(.+)$`', $user_filter, $p)) {
                            self::$allowed = array_key_exists($p[1], self::$attributes) && preg_match('`'.$p[2].'`', self::$attributes[$p[1]]);
                        }
                    } else {
                        self::$allowed = !(bool)$user_filter;
                    }
                    
                    if (!self::$allowed) {
                        self::$type = null;
                        return;
                    }
                }
                
                if (!array_key_exists('additional', self::$attributes)) {
                    self::$attributes['additional'] = array();
                }
                
                // Add name to additional attributes by default so that we can use it when sending out emails
                if (!array_key_exists('name', self::$attributes['additional'])) {
                    if (array_key_exists('name', self::$attributes)) {
                        self::$attributes['additional']['name'] = self::$attributes['name'];
                    } elseif (array_key_exists('remote_application', self::$attributes)) {
                        self::$attributes['additional']['name'] = self::$attributes['remote_application'];
                    } elseif (array_key_exists('uid', self::$attributes)) {
                        self::$attributes['additional']['name'] = self::$attributes['uid'];
                    } else {
                        throw new AuthAuthenticationNotFoundException();
                    }
                }
                
                // Set user if got uid attribute
                self::$user = User::fromAttributes(self::$attributes);
                // if we change anything interesting we want to subvert the
                // too frequent save check in recordActivity
                $forceSave = false;
                
                // Save user additional attributes if enabled
                if (self::isSP() && Config::get('auth_sp_save_user_additional_attributes')) {
                    self::$user->additional_attributes = self::$attributes['additional'];
                }
                
                // Save user quota for guest uploads
                $user_quota = Config::get('user_quota');
                if ($user_quota) {
                    if (self::$user->quota != $user_quota) {
                        $forceSave = true;
                        self::$user->quota = $user_quota;
                    }
                }
                
                self::$user->recordActivity($forceSave); // Saves preferences and all above changes
            }
        }
        
        return self::$user;
    }

    /**
     * Return current user if it exists.
     *
     * see the private user_protected() for implementation.
     *
     * implementation note: this nests a call to user_protected() so that
     * authClassLoadingCount can be incr/decr paired around the auth work
     *
     * This allows code to call other methods that might themselves want
     * to indirectly call auth::user(). This can happen for example if
     * any code calls Logger::info() or the like as that code might well
     * call user() on your behalf. Note that if user() is called from code
     * inside user() then the nested call will return false, which is better
     * than infinite recursion but might not be quite what you expected.
     *
     * @return User instance or false
     */
    public static function user()
    {
        if (self::$exception) {
            throw self::$exception;
        }

        if (self::$authClassLoadingCount) {
            return false;
        }

        try {
            self::$authClassLoadingCount++;

            $ret = self::user_protected();
            self::$authClassLoadingCount--;
            return $ret;
        } catch (Exception $e) {
            self::$authClassLoadingCount--;
            self::$exception = $e;
            throw $e;
        }
    }

    /**
     * Return current user or guest if it exists.
     */
    public static function getPrincipal()
    {
        $principal = null;
        
        if (Auth::isGuest()) {
            $principal = AuthGuest::getGuest();
        }
        if( !$principal ) {
            $principal = Auth::user();
        }
        return $principal;
    }

    /**
     * Reset the current user to the given data, creating database records if needed.
     * This function should only be called from testing code that is executed locally.
     *
     * @return User instance or false
     */
    public static function testingForceToUser($uid, $email, $name = null)
    {
        $userAttributes['uid']   = $uid;
        $userAttributes['email'] = $email;
        $userAttributes['name']  = $name;
        AuthLocal::setUser(null, null);
        AuthLocal::setUser($uid, $email, $name);
        $user = User::fromAttributes($userAttributes);
        $user->recordActivity();
        self::$user = $user;
        return $user;
    }

    /**
     * Tells if an user is connected.
     *
     * @retrun bool
     */
    public static function isAuthenticated($critical = true)
    {
        if (!self::$allowed) {
            throw new AuthUserNotAllowedException();
        }

        // command line cron, install, upgrade etc do not need a session
        if (Logger::isLocalProcess()) {
            return true;
        }
        
        try {
            return (bool)self::user();
        } catch (Exception $e) {
            if ($critical) {
                throw $e;
            }
            return false;
        }
    }
    
    /**
     * Retreive attributes
     *
     * @return array
     */
    public static function attributes()
    {
        return self::$attributes;
    }

    /**
     * Tells if the current user has a given privilege. 
     * 
     * If there is a setting in config.php for auth_sp_saml_$configName_entitlement
     * then SAML will determine if the user has this access. Otherwise the
     * array in config.php for $configName will show an explicit list of users who
     * have this access.
     *
     * @retrun bool
     */
    public static function isPrivilegeAllowed( $configName )
    {
        $ret = false;

        if (is_null($configName))
            return false;

        
        if (self::user()) {
            // constants
            $entitlement_attribute = Config::get('auth_sp_saml_entitlement_attribute');
            $additional_attributes = Config::get('auth_sp_additional_attributes');

            // settings for this configName
            $cfg = Config::get($configName);
            $cfg_entitlement = Config::get('auth_sp_saml_' .$configName. '_entitlement');
            
            // Admin privs through entitlement
            if (self::isSP()
                && $entitlement_attribute
                && $cfg_entitlement
                && $additional_attributes)
            {
                if (self::$attributes['additional'] 
                    && array_key_exists($entitlement_attribute, self::$attributes['additional']) 
                    && in_array($entitlement_attribute, $additional_attributes))
                {

                    $ret = in_array($cfg_entitlement,
                                    self::$attributes['additional'][$entitlement_attribute]);
                }
            }
            else
            {
                // Admin UID from config file
                if (!is_array($cfg)) {
                    $cfg = array_filter(array_map('trim', preg_split('`[,;\s]+`', (string)$cfg)));
                }

                $ret = in_array(self::user()->saml_user_identification_uid, $cfg);
            }
        }

        return $ret;
    }
    
    /**
     * Tells if the current user is an admin.
     *
     * @retrun bool
     */
    public static function isAdmin()
    {
        if (is_null(self::$isAdmin)) {
            self::$isAdmin = false;

            if (self::user()) {
                $admin = Config::get('admin');
                $entitlement_attribute = Config::get('auth_sp_saml_entitlement_attribute');
                $admin_entitlement = Config::get('auth_sp_saml_admin_entitlement');
                $additional_attributes = Config::get('auth_sp_additional_attributes');
                // Admin privs through entitlement
                if (self::isSP() && $entitlement_attribute && $admin_entitlement && $additional_attributes)  {
                    if (self::$attributes['additional'] &&
                        array_key_exists($entitlement_attribute, self::$attributes['additional']) &&
                        in_array($entitlement_attribute, $additional_attributes)) {

                        self::$isAdmin = in_array($admin_entitlement, self::$attributes['additional'][$entitlement_attribute]);
                    }
                } else {
                    // Admin UID from config file
                    if (!is_array($admin)) {
                        $admin = array_filter(array_map('trim', preg_split('`[,;\s]+`', (string)$admin)));
                    }

                    self::$isAdmin = in_array(self::user()->saml_user_identification_uid, $admin);
                }
            }
        }

        return self::$isAdmin && !self::isGuest();
    }

    public static function canViewAggregateStatistics()
    {
        if (is_null(self::$canViewAggregateStats)) {
            self::$canViewAggregateStats = self::isPrivilegeAllowed( 'can_view_aggregate_statistics' )
                                        && !self::isGuest();
        }

        return self::$canViewAggregateStats;
    }

    public static function canViewStatistics()
    {
        if (is_null(self::$canViewStats)) {
            self::$canViewStats = self::isPrivilegeAllowed( 'can_view_statistics' )
                               && !self::isGuest();
        }

        return self::$canViewStats;
    }
    
    /**
     * Get auth type
     *
     * @return mixed
     */
    public static function type()
    {
        return self::$type;
    }
    
    /**
     * Tells if the user is given by an authenticated remote application.
     *
     * @return bool
     */
    public static function isRemoteApplication()
    {
        return self::isRemote() && AuthRemote::application();
    }
    
    /**
     * Tells if the user authenticated in a remote fashion.
     *
     * @return bool
     */
    public static function isRemoteUser()
    {
        return self::isRemote() && !AuthRemote::application();
    }
    
    /**
     * Tells if the user authenticated in a remote fashion or is from a remote application.
     *
     * @return bool
     */
    public static function isRemote()
    {
        return self::$type == 'remote';
    }
    
    /**
     * Tells if the user authenticated through a service provider.
     *
     * @return bool
     */
    public static function isSP()
    {
        return self::$type == 'sp';
    }
    
    /**
     * Tells if the user authenticated through a local service.
     *
     * @return bool
     */
    public static function isLocal()
    {
        return self::$type == 'local';
    }
    
    /**
     * Tells if the user authenticated as guest.
     *
     * @return bool
     */
    public static function isGuest()
    {
        return self::$type == 'guest';
    }

    /**
     * The user or the guest current performing the action 
     */
    public static function actor()
    {
        if( self::isGuest() ) {
            return AuthGuest::getGuest();
        }
        return self::User();
    }

    /**
     * Tells if a session is started.
     *
     * @return bool
     */
    public static function isSessionStarted()
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
}
