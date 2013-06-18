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

//  --------------------------------
// SAML authentication
//  --------------------------------

class AuthSaml {

    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 

    // checks if a user is SAML authenticated and is administrator: returns true/false
    // admins can be added in the configuration file using the configured $config['saml_uid_attribute']
    public function authIsAdmin() {

        global $config;

        require_once($config['site_simplesamllocation'].'lib/_autoload.php');

        $as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']); 
        if($as->isAuthenticated()) {
            $as->requireAuth();
            $attributes = $as->getAttributes();

            // compare config admin to userUID
            if(isset($attributes[$config['saml_uid_attribute']][0])) {
                $attributes["saml_uid_attribute"] = $attributes[$config['saml_uid_attribute']][0];
            } else if(isset($attributes[$config['saml_uid_attribute']])) {
                $attributes["saml_uid_attribute"] = $attributes[$config['saml_uid_attribute']];
            } else {
                // required attribute does not exist
                logEntry("UID attribute not found in IDP (".$config['saml_uid_attribute'].")","E_ERROR");
                return FALSE;
            }

            $known_admins = array_map('trim',explode(',', $config['admin']));
            if(in_array($attributes["saml_uid_attribute"], $known_admins)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;

    }

    // returns SAML authenticated user information as josn array
    public function sAuth() {

      	global $config;

        require_once($config['site_simplesamllocation'].'lib/_autoload.php');

        $as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
        $as->requireAuth();
        $attributes = $as->getAttributes();
        $missing_attributes = FALSE ;

        // need to capture email from SAML attribute. may be single attribute or array 
        // ensure that it's always an array.
        if(isset($attributes[$config['saml_email_attribute']])) {
                if ( is_array($attributes[$config['saml_email_attribute']]) ) {
                        $attributes["email"] = $attributes[$config['saml_email_attribute']];
                } else {
                        $attributes["email"] = array($attributes[$config['saml_email_attribute']]);
                }
        } 

        // Check for empty or invalid email attribute
        if ( empty($attributes["email"]) ) {
            logEntry("No valid email attribute found in IDP (looking for '".$config['saml_email_attribute']."')","E_ERROR");
            $missing_attributes = TRUE ;
        }
        foreach ($attributes["email"] as $email) {
                if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
                        logEntry("Invalid email attribute received from IdP: '".$email."'","E_ERROR");
                        $missing_attributes = TRUE ;
                }
        }

        if(isset($attributes[$config['saml_name_attribute']][0])) {
            $attributes["cn"] = $attributes[$config['saml_name_attribute']][0];
        }
        if(!isset($attributes[$config['saml_name_attribute']]) && isset($attributes["email"])) {
            $attributes["cn"] =   substr($attributes["email"],0, strpos($attributes["email"] , "@")) ;
        }

        if(isset($attributes[$config['saml_uid_attribute']][0])) {
            $attributes["saml_uid_attribute"] = $attributes[$config['saml_uid_attribute']][0];
        } else if (isset($attributes[$config['saml_uid_attribute']])) {
            $attributes["saml_uid_attribute"] = $attributes[$config['saml_uid_attribute']];
        } else {
            // Required UID attribute missing
            logEntry("UID attribute not found in IDP (looking for '".$config['saml_uid_attribute']."')","E_ERROR");
            $missing_attributes = TRUE ;
        }

        // logs access by a user and users logged on array data
        // this could be moved to logging function in future versions
        $inglue = '='; 
        $outglue = '&';
        $valsep = '|';
        $message = "";

        foreach ($attributes as $tk => $tv) {
            $message .= (isset($return) ? $return . $outglue : '') . $tk . $inglue . (is_array($tv) ? implode($valsep, $tv) : $tv) . $outglue;
        }

        $ip = $_SERVER['REMOTE_ADDR'];//capture IP

        if($config['dnslookup'] == true) {
            $domain = gethostbyaddr($ip);
        } else {
            $domain = "";
        } 

        $message .= "[".$ip."(".$domain.")] ".$_SERVER['HTTP_USER_AGENT'];

        $attributes["SessionID"] = session_id();

        if ($missing_attributes) {
            logEntry($message, "E_ERROR");
            return "err_attributes";
        } else {
            logEntry($message, "E_NOTICE");
            return $attributes;
        }
    }

    // requests logon URL from SAML and returns string
    public function logonURL() {

        global $config;
        //require_once($config['site_simplesamllocation'].'lib/_autoload.php');
        //$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
        $lognurl = $config['site_simplesamlurl']."module.php/core/as_login.php?AuthId=".$config['site_authenticationSource']."&ReturnTo=".$config['site_url']."index.php?s=upload";
        return htmlentities($lognurl); //$attributes;
    }

    // requests logon OFF URL from SAML and returns string	
    public function logoffURL() {
		global $config;
		require_once($config['site_simplesamllocation'].'lib/_autoload.php');
    	//$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
    	$logoffurl = $config['site_simplesamlurl']."module.php/core/as_logout.php?AuthId=".$config['site_authenticationSource']."&ReturnTo=".$config['site_logouturl']."" ;
        return htmlentities($logoffurl); //$attributes;
    }

    // checks SAML for autheticated user: returns true/false	
	// return bool if authenticated
    public function isAuth() {
        global $config;
        require_once($config['site_simplesamllocation'].'lib/_autoload.php');
        $as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
        return $as->isAuthenticated();
    }
}

?>
