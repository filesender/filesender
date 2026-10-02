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
 * Remote User (Header-based) Authentication
 *
 * This authenticator trusts the Remoteuser server variable
 * which is typically set by Apache mod_auth_*, nginx auth_request,
 * or other reverse proxy authentication mechanisms.
 * For testing, this can be set in the fastcgi_params
 */
class AuthSPHeader {
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
     * Check if user is authenticated via REMOTE_USER
     *
     * @return bool
     */
    public static function isAuthenticated() {
        return self::getRemoteUser() !== null;
    }

    /**
     * Get the REMOTE_USER value from server variables
     *
     * @return string|null
     */
    private static function getRemoteUser() {
        $remoteUser = null;

        // Get headers and check for Remoteuser
        foreach (getallheaders() as $name => $value) {
            if ($name == "Remoteuser"){
                $remoteUser = $value;
            }
        }

        if ($remoteUser == null){
            Logger::warn("Remote user is null");
            $remoteUser = 'null';
        }

        // Sanitize the username
        if ($remoteUser !== null) {
            $remoteUser = trim($remoteUser);
            if (empty($remoteUser)) {
                Logger::warn("Remote user is empty, returning null");
                return null;
            }
        }

        return $remoteUser;
    }

    /**
     * Get user attributes
     *
     * @return array
     */
    public static function attributes() {
        if (self::$attributes !== null) {
            return self::$attributes;
        }

        $remoteUser = self::getRemoteUser();

        if ($remoteUser === null) {
            return array();
        }

        // We generate a uid from the byte array of the username - needs a proper mapping/implementation
        $uid = 0;
        $byte_array = unpack('C*', $remoteUser);
        foreach ($byte_array as $value) {
            $uid += $value;
        }

        // Build attributes array
        self::$attributes = array(
            'uid' => $remoteUser,
            'email' => array(self::deriveEmail($remoteUser)),
            'name' => array(self::deriveName($remoteUser)),
            'idp' => 'header',
        );

        // Add additional attributes from headers if available
        if (!empty($_SERVER['HTTP_REMOTE_USER_EMAIL'])) {
            self::$attributes['email'] = array($_SERVER['HTTP_REMOTE_USER_EMAIL']);
        }

        if (!empty($_SERVER['HTTP_REMOTE_USER_NAME'])) {
            self::$attributes['name'] = array($_SERVER['HTTP_REMOTE_USER_NAME']);
        }

        return self::$attributes;
    }

    /**
     * Derive email from username
     *
     * @param string $username
     * @return string
     */
    private static function deriveEmail($username) {
        // If username is already an email, return it
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return $username;
        }

        // Otherwise, try to construct email from config
        $defaultDomain = Config::get('auth_remote_user_default_domain');
        if ($defaultDomain) {
            return $username . '@' . $defaultDomain;
        }

        // Fallback: return username as-is
        return $username;
    }

    /**
     * Derive display name from username
     *
     * @param string $username
     * @return string
     */
    private static function deriveName($username) {
        // Remove domain part if email
        if (strpos($username, '@') !== false) {
            $username = substr($username, 0, strpos($username, '@'));
        }

        // Convert underscores/dots to spaces and capitalize
        $name = str_replace(array('_', '.'), ' ', $username);
        return ucwords($name);
    }

    /**
     * Trigger authentication (redirect or display login)
     * For header-based auth, we just return - the upstream handles login
     */
    public static function trigger() {
        // If not authenticated, return 401
        if (!self::isAuthenticated()) {
            header('HTTP/1.1 401 Unauthorized');
            die('Authentication required. Please ensure you are accessing this through the authenticated proxy.');
        }
    }

    /**
     * Logout the user
     */
    public static function logout() {
        // Clear any session data
        self::$attributes = null;

        // Get logout URL from config if set
        $logoutUrl = Config::get('auth_remote_user_logout_url');
        if ($logoutUrl) {
            header('Location: ' . $logoutUrl);
            exit;
        }
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

    public static function ensureLocalIdPMetadata( $entityId, $idp, $force = false )
    {
        return;
    }
}
