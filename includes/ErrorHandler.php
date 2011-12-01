<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
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
 	// error handling functions
 	// --------------------------------
 
	// catch errors (and don't display them) unless debug is true
	// custom exception logs to syslog 
	// custom exception logs to config log folder 
	
function customException($exception){

	$exceptionMsg = sprintf(
		"Exception: [%s] %s : %s [%s] ",
		$exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine()
	);

	// syslog
	syslog($exception->getCode(),$exceptionMsg);
	// log to local log file
	logEntry($exceptionMsg);

	exit;

}

// cusom errors
function customError($errno, $errstr, $errfile,$errline){

	$errMsg = "Error: [$errno] $errstr : $errfile [$errline] ";
	// syslog
	syslog($errno,$errMsg);
	// log to local log file
	logEntry($errMsg);

	return;

}

// general log function for flex logging
function logEntry($message){
	
	global $config;
	
	if($config["debug"] ) {
	if(isset($config['log_location'])) 
	{
	date_default_timezone_set($config['Default_TimeZone']);

	if(isset($_SERVER['REMOTE_ADDR']))
	{	
		$ip = $_SERVER['REMOTE_ADDR']; //capture IP
	
		if($config['dnslookup'] == true) {
			$domain = GetHostByName($ip);
		} else {
			$domain = "";
		}
	} else {
		$ip = "none";	
		$domain = "none";	
	}
	
	$message .= "[".$ip."(".$domain.")] ";
	$dateref = date("Ymd");
	$data = date("Y/m/d H:i:s");
	$myFile = $config['log_location'].$dateref.".log.txt";
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

ini_set('display_errors', 'On');

// if debug is on then set the custom error handler
if($config['debug'] == true || $config['debug'] == 1){

	ini_set('log_errors', 'On');
	if (defined('E_DEPRECATED')) {
		set_error_handler("customError",E_ALL & ~E_DEPRECATED);
	}
	else {
		set_error_handler("customError",E_ALL);
	}
	set_exception_handler("customException");
}

function displayError($errmsg)
{
	global $config;
	logEntry($errmsg);
	if($config['displayerrors'] )
	{
		echo "<br><div id='errmessage'>".$errmsg."</div>";
	}
}
?>
