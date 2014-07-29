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

/*********************************
 *  SAML authentication.         *
 *********************************/
class AuthSaml
{
    // Stores "local" instance of SimpleSAML_Auth_Simple
    private static $authsimple = null;

    // Initializing needed config directives, even those that are used ONCE
    private static $saml = array();
    private static $site = array();
    private static $admin = null;
    private static $dnslookup = false;


    /**
     *  returns current instance of AuthSaml, or instantiates a new object of that type
     *
     */
    /*public static function getInstance()
    {
        // Returns existing instance, initiates new one otherwise
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }*/

    /**
     *  The constructor method
     *  sets the $authsimple property to a new /running instance of SimpleSAML_Auth_Simple
     *  
     */
    /*private function __construct()
    {
	if (is_null(self::$authsimple)) {
            self::$site      = Config::get('site_*');
            self::$saml      = Config::get('saml_*');
            self::$admin     = Config::get('admin');
            self::$dnslookup = Config::get('dnslookup');

            require_once(self::$site['simplesamllocation'] . 'lib/_autoload.php');
            self::$authsimple = new SimpleSAML_Auth_Simple(self::$site['authenticationSource']);
	}
    }*/
    
    /**
     *  initialise(): will replace the constructor in the static class
     *  implementation of AuthSaml.class
     *
     *  sets the $authsimple property to a new /running instance of SimpleSAML_Auth_Simple
     *  
     */
    private static function initialise()
    {
        if (!isset(self::$site['simplesamllocation']) || !isset(self::$saml)
            || !isset(self::$admin) || !isset(self::$dnslookup)) {
            self::$site = Config::get('site_*');
            self::$saml = Config::get('saml_*');
            self::$admin = Config::get('admin');
            self::$dnslookup = Config::get('dnslookup');
        }

	if (is_null(self::$authsimple)) {
            require_once(self::$site['simplesamllocation'] . 'lib/_autoload.php');
            self::$authsimple = new SimpleSAML_Auth_Simple(self::$site['authenticationSource']);
        }
    }

    /** 
     * Checks SAML for authenticated user
     * @return bool isAuthenticated()
     */
    public function isAuth()
    {
        self::initialise();
        return self::$authsimple->isAuthenticated();
    }

    
    // gets a user's attributes from the IdP through the running SimpleSAML_Auth_Simple instance
    private static function getAttribs()
    {
        if (isAuth()) {
            return self::$authsimple->getAttributes();
        } else {
            return null;
        }
    }


    /**
     * Checks if a user is SAML authenticated and is administrator: returns true/false.
     * Admins can be added in the configuration file using the configured 'saml_uid_attribute'
     *
     */
    public function authIsAdmin()
    {
        self::initialise();
        $attributes = self::getAttribs();
        
        if (!is_null($attributes)) {
            // Compare config admin to userUID.
            if (isset($attributes[self::$saml['uid_attribute']][0])) {
                $attributes['saml_uid_attribute'] =
                $attributes[self::$saml['uid_attribute']][0];
            } elseif (isset($attributes[self::$saml['uid_attribute']])) {
                $attributes['saml_uid_attribute'] = $attributes[self::$saml['uid_attribute']];
            } else {
                // Required attribute does not exist.
                $userMsg = "user ".self::$saml['uid_attribute']." is not authenticated";
                $logMsg = "UID attribute not found in IDP (".self::$saml['uid_attribute'].")";
                throw new UserNotAuthException($userMsg, $logMsg, false);

                return false;
            }
            $knownAdmins = array_map('trim', explode(',', self::$admin));
            
            return in_array($attributes['saml_uid_attribute'], $knownAdmins);
        }

        return false;
    }

    // Returns SAML authenticated user information as json array.
    public function sAuth()
    {
        self::initiliase();
        $attributes = self::getAttribs();
        $missingAttributes = false;

        // need to capture email from SAML attribute. may be single attribute or array 
        // ensure that it's always an array.
        if (isset($attributes[self::$saml['email_attribute']])) {
            if (is_array($attributes[self::$saml['email_attribute']])) {
                $attributes['email'] = $attributes[self::$saml['email_attribute']];
            } else {
                $attributes['email'] = array($attributes[self::$saml['email_attribute']]);
            }
        }

        // Check for empty or invalid email attribute
        if (empty($attributes["email"])) {
            $lMsg = "No valid email attribute found in IDP (looking for '" . 
                self::$saml['email_attribute'] .
                "')";
            throw new AuthAttrException("", $lMsg, true);
            $missingAttributes = true;
        }
        
        // checks if the mail addresse(es) in the mail attrib is (are) valid
        // should be moved to a data validation class
        foreach ($attributes["email"] as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $lMsg = "Invalid email attribute received from IdP: '" . $email . "'";
                throw new AuthAttrException("", $lMsg, true);
                $missingAttributes = true;
            }
        }

        if (isset($attributes[self::$saml['name_attribute']][0])) {
            $attributes['cn'] = $attributes[self::$saml['name_attribute']][0];
        }

        if (!isset($attributes[self::$saml['name_attribute']]) && isset($attributes['email'])) {
            $attributes['cn'] = substr($attributes['email'], 0, strpos($attributes['email'], '@'));
        }

        if (isset($attributes[self::$saml['uid_attribute']][0])) {
            $attributes['saml_uid_attribute'] = $attributes[self::$saml['uid_attribute']][0];
        } elseif (isset($attributes[self::$saml['uid_attribute']])) {
            $attributes['saml_uid_attribute'] = $attributes[self::$saml['uid_attribute']];
        } else {
            // Required UID attribute missing.
            $lMsg = "UID attribute not found in IDP (looking for '" . self::$saml['uid_attribute'] . "')";
            throw new AuthAttrException("", $lMsg, true);
            $missingAttributes = true;
        }

        // Logs access by a user and users logged on array data.
        // This could be moved to logging function in future versions.
        $inGlue = '=';
        $outGlue = '&';
        $separator = '|';
        $message = '';

        foreach ($attributes as $tk => $tv) {
            $message .= (isset($return) ? $return . $outGlue : '') . $tk . $inGlue . 
                (is_array($tv) ? implode($separator, $tv) : $tv) . $outGlue;
        }

        $ip = $_SERVER['REMOTE_ADDR']; // Capture IP.

        if (self::$dnslookup == true) {
            $domain = gethostbyaddr($ip);
        } else {
            $domain = '';
        }

        $message .= '[' . $ip . '(' . $domain . ')] ' . $_SERVER['HTTP_USER_AGENT'];
        $attributes['SessionID'] = session_id();
        
        throw new AuthAttrException($message, $missingAttributes);  //logs errors
        return $missingAttributes ? 'err_attributes' : $attributes;
    }

    // Requests logon URL from SAML and returns string.
    public function logonURL()
    {
        self::initialise();
        $logonUrl = self::$site['simplesamlurl'] . 'module.php/core/as_login.php?AuthId=' . 
            self::$site['authenticationSource'] . '&ReturnTo=' . self::$site['url'] . 'index.php?s=upload';
        return htmlentities($logonUrl);
    }

    // Requests log OFF URL from SAML and returns string.
    public function logoffURL()
    {
        self::initialise();
        $logoffUrl = self::$site['simplesamlurl'] . 'module.php/core/as_logout.php?AuthId=' . 
            self::$site['authenticationSource'] . '&ReturnTo=' . self::$site['site_logout'];
        return htmlentities($logoffUrl);
    }
}

/** 
 *  Default exception class for authentication-related exceptions. Outputs info to log.
 *
 *  @param string $lMsg: message to write to log.
 *  @param bool $error:  Type of log entry. E_ERROR if true, E_NOTICE if false.
 */
class AuthException extends Exception
{
    public function __construct($lMsg, $error)
    {
        if ($error) {
            logEntry($lMsg, "E_ERROR");
        } else {
            logEntry($lMsg, "E_NOTICE");
        }
    }
}

/**
 *  thrown when the method requires a user to be authenticated and he isn't.
 *  @params $log = parent::$lMsg, $error = parent::$error
 *  @param string $uMsg: message to user.
 */
class UserNotAuthException extends AuthException
{
    public function __construct($uMsg, $log, $error)
    {
        parent::__construct($log, $error);
        //TO_USER($uMsg);
    }
}

/**
 * Exception that is caused by missing or invalid authentication attributes
 */
class AuthAttrException extends AuthException
{
    public function __construct($uMsg, $log, $error)
    {
        parent::__construct($log, $error);
        //TO_USER($uMsg);
    }
}
