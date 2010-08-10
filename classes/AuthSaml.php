<?php

/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 
class AuthSaml {

private static $instance = NULL;

	public static function getInstance() {
		// Check for both equality and type		
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	} 

	public function authIsAdmin() {

		$CFG = config::getInstance();
		$config = $CFG->loadConfig();
		
		require_once($config['site_simplesamllocation'].'lib/_autoload.php');
				
		$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']); 
		if($as->isAuthenticated()) {
		$as->requireAuth();
		$attributes = $as->getAttributes();
		
		// compare config admin to userUID
		if(isset($attributes[$config['saml_uid_attribute']][0])) {
		$attributes["eduPersonTargetedID"] = $attributes[$config['saml_uid_attribute']][0];
		}
		
		if(stristr($config['admin'], $attributes["eduPersonTargetedID"]) === FALSE) {
    	return FALSE;
  		} else {
		return TRUE;
		}
	}
		return FALSE;
			
	}

	public function sAuth() {
	
			$CFG = config::getInstance();
			$config = $CFG->loadConfig();
			
			require_once($config['site_simplesamllocation'].'lib/_autoload.php');
				
			$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
			
			$as->requireAuth();
			$attributes = $as->getAttributes();
			
		// check for multidimensional array	
		
		// need to capture email from SAML attribute
		// may be single attribute or array 
		if(isset($attributes[$config['saml_email_attribute']])) {
		$attributes["email"] = $attributes[$config['saml_email_attribute']];
		}
		
		if(isset($attributes[$config['saml_email_attribute']][0])) {
		$attributes["email"] = $attributes[$config['saml_email_attribute']][0];
		}
		
		
		if(isset($attributes[$config['saml_name_attribute']][0])) {
		$attributes["cn"] = $attributes[$config['saml_name_attribute']][0];
		}
		if(!isset($attributes[$config['saml_name_attribute']])) {
		$attributes["cn"] =   substr($attributes["email"],0, strpos($attributes["email"] , "@")) ;
		}
		 
		if(isset($attributes[$config['saml_uid_attribute']][0])) {
		$attributes["eduPersonTargetedID"] = $attributes[$config['saml_uid_attribute']][0];
		}
		
		$inglue = '='; 
		$outglue = '&';
		$message = "";
		
	   foreach ($attributes as $tk => $tv) {
     	 $message .= (isset($return) ? $return . $outglue : '') . $tk . $inglue . $tv . $outglue;
    	}
		
		 $ip = $_SERVER['REMOTE_ADDR'];//capture IP
	 	
		 if($config['dnslookup'] == true) {
	 $domain = gethostbyaddr($ip);
	 } else {
	 $domain = "";
	 } 
	 
		$message .= "[".$ip."(".$domain.")] ".$_SERVER['HTTP_USER_AGENT'];
	 		
  		$dateref = date("Ymd");
		$data = date("Y/m/d H:i:s");
		$myFile = $config['log_location'].$dateref."-error.log.txt";
		$fh = fopen($myFile, 'a') or die("can't open file");
		$stringData = $data.' [Session ID: '.session_id().'] '.$message."\n";
		fwrite($fh, $stringData);
		fclose($fh);
		closelog();
		
			//print_r($attributes);
			$attributes["SessionID"] = session_id();
			return $attributes;
		}
		
	public function logonURL() {
	
			$CFG = config::getInstance();
			$config = $CFG->loadConfig();
			
			//require_once($config['site_simplesamllocation'].'lib/_autoload.php');
			//$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
			$lognurl = $config['site_simplesamlurl']."module.php/core/as_login.php?AuthId=".$config['site_authenticationSource']."&ReturnTo=".$config['site_url']."";
				
			return $lognurl; //$attributes;
		}
		
		public function logoffURL() {
	
			$CFG = config::getInstance();
			$config = $CFG->loadConfig();
			
			//require_once($config['site_simplesamllocation'].'lib/_autoload.php');
			//$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
			
			$logoffurl = $config['site_simplesamlurl']."module.php/core/as_logout.php?AuthId=".$config['site_authenticationSource']."&ReturnTo=".$config['site_logouturl'] ;
				
			return $logoffurl; //$attributes;
		}
		
	public function isAuth() {
	
			// return bool if authenticated
			$CFG = config::getInstance();
			$config = $CFG->loadConfig();
			
			require_once($config['site_simplesamllocation'].'lib/_autoload.php');
				
			$as = new SimpleSAML_Auth_Simple($config['site_authenticationSource']);
			return $as->isAuthenticated();
			
		}

}
	
?>