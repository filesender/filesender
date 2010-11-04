<?php

/*
 *  Filesender www.filesender.org
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
 
/*
 * log functions
 */
	

class Log {

private static $instance = NULL;

	public static function getInstance() {
		// Check for both equality and type		
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	} 

//---------------------------------------
// Save Log Data
//
public function saveLog($dataitem,$logType,$message){

	$db = DB::getInstance();
	$CFG = config::getInstance();
	$config = $CFG->loadConfig();
	
	$authsaml = AuthSaml::getInstance();
	if( $authsaml->isAuth()) {
		$authAttributes = $authsaml->sAuth();
	} else {
		$authAttributes["eduPersonTargetedID"] = "";
	}
	$dbCheck = DB_Input_Checks::getInstance();


	// If authenticated also add authID to log
	// add os, browser and gears version to log message
	if(isset($dataitem['fileuid'])) 
	{
	$logfileuid	= $dataitem['fileuid'];
	$logvoucheruid	= $dataitem['filevoucheruid'];
	$logtype	= $logType;
	$logfrom	= $dataitem['filefrom'];
	$logto	= $dataitem['fileto'];
	$logdate	= date($config['postgresdateformat'], time());//use timestamp with timezone $dbCheck->checkString(pg_escape_string($dataitem['logdate']));
	$logfilesize	= $dataitem['filesize'];
	$logfilename	= $dataitem['fileoriginalname'];
	$logmessage	= $message;
	$logauthuseruid	= $authAttributes["eduPersonTargetedID"];
	} else {
	$logfileuid	= "";
	$logvoucheruid	= "";
	$logtype	= $logType;
	$logfrom	= "";
	$logto	= "";
	$logdate	= date($config['postgresdateformat'], time());//use timestamp with timezone $dbCheck->checkString(pg_escape_string($dataitem['logdate']));
	$logfilesize	= "";
	$logfilename	= "";
	$logmessage	= $message;
	$logauthuseruid	= $authAttributes["eduPersonTargetedID"];
	}
	
	$sqlQuery	= "
					INSERT INTO 
							logs 
						(
							logfileuid,
							logvoucheruid, 
							logtype , 
							logfrom, 
							logto, 
							logdate, 
							logfilesize, 
							logfilename, 
							logmessage,
							logauthuseruid
						) 
					VALUES 
						(
							'%s',
							'%s', 
							'%s', 
							'%s', 
							'%s',
							'%s',
							%d,
							'%s',
							'%s',
							'%s'
						)";

	$result = $db->fquery(
						$sqlQuery,
						$logfileuid,
						$logvoucheruid,
						$logtype,
						$logfrom,
						$logto,
						$logdate,
						$logfilesize,
						$logfilename,
						$logmessage,
						$logauthuseruid
					 ) or die("Error");
	
	// error in log file
		if(!$result){
			return  false;
		} else {
			return true;
		}
	
	}
	
	// logfile for individual client specific logging
	// calls to this function are form glex/flash if client specific logging is on

public function logProcess($client,$message)
	{
	global $config;
	
	if($config["debug"] or $config["client_specific_logging"])
	{
		$ip = $_SERVER['REMOTE_ADDR']; //capture IP
	
		if($config['dnslookup'] == true) {
			$domain = GetHostByName($ip);
		} else {
			$domain = "";
		}
	
		$message .= "[".$ip."(".$domain.")] ";
		$dateref = date("Ymd");
		$data = date("Y/m/d H:i:s");
		$myFile = $config['log_location'].$dateref."-".$client.".log.txt";
		$fh = fopen($myFile, 'a') or die("can't open file");
		// don't print errors on screen when there is no session.
		if(isset($_REQUEST['PHPSESSID'])){
			$sessionId = $_REQUEST['PHPSESSID'];
		} else {
			$sessionId = "none";
		}
		$stringData = $data.' [Session ID: '.$sessionId.'] '.$message."\n";
		fwrite($fh, $stringData);
		fclose($fh);
		closelog();
		}
	}
}

?>
