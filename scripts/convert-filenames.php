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
// this file is manually invoked to rename all non-expired files in the filestore
// to the new naming scheme introduced in beta 0.1.17. 
// 
// Usage: php convert-filenames.php [revert]
//
//  where 'revert' is an optional parameter to revert the filenames to the
//  old naming scheme in case you want to switch back to a pre-0.1.17 release
//  The new naming scheme was introduced in svn revision r334
// ---------------------------------

 
// required as this page is called from CONVERT not from a web browser
chdir(dirname(__FILE__));

// force all error reporting
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_DEPRECATED);
}       
else {  
	error_reporting(E_ALL);
}       

$filesenderbase = dirname(dirname(__FILE__));

// include all required classes
require_once("$filesenderbase/config/config.php");
require_once("$filesenderbase/classes/DBAL.php");
require_once("$filesenderbase/classes/Mail.php");
require_once("$filesenderbase/classes/DB_Input_Checks.php");
require_once("$filesenderbase/classes/Functions.php");

global $config;
$sendmail = Mail::getInstance();

// set time zone for this session
date_default_timezone_set($config['Default_TimeZone']);

// check if session already exists
if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 
// Check arguments passed
if ($argc > 1 ) {
    if ($argv[1] == "revert") {
       $rename_to_old_scheme = TRUE ;
    }
} else {
       $rename_to_old_scheme = FALSE ;
}

// log that conversion has started running
logProcess("CONVERT","Conversion started");
if (convertNames($rename_to_old_scheme))
{
	// conversion completed - log
	logProcess("CONVERT","Conversion Complete");
} 
	else 
{
	// email admin - error in Conversion
	logProcess("CONVERT","Conversion Error - check error log");
}



//---------------------------------------
// Rename stored files to new naming scheme (or back)
function convertNames($rename_to_old_scheme) 
	{
	
	global $config;
	$db = DBAL::getInstance();
	
	// check log_location exists	
	if (!file_exists($config["log_location"])) {
	logProcess("CONVERT","Unable to find log_location location specified in config.php  :".$config["log_location"]);
	return false;
	}
	
	// check site_filestore exists
	if (!file_exists($config["site_filestore"])) {
		logProcess("CONVERT","Unable to find site_filestore location specified in config.php  :".$config["site_filestore"]);
		return false;
	}	
	
	// check site_temp_filestore exists
	if (!file_exists($config["site_temp_filestore"])) {
		logProcess("CONVERT","Unable to find site_temp_filestore location specified in config.php  :".$config["site_temp_filestore"]);
		return false;
	}	
	
	$FilestoreDirectory = $config["site_filestore"];

	
	// Final cleanup is to close any records in the database that do not have a physical file attached to them
	// close all entries that do not have a pyhsical file in storage
	try {
		$search = $db->query("SELECT * FROM files WHERE filestatus = 'Available'"); 
	} catch (DBALException $e) {
		logProcess("CONVERT","SQL Error on updating files".$e->getMessage());
		return FALSE;
	}
		
	// check for empty result in SQL
	if (empty($search)) { 
		logProcess("CONVERT","SQL Error on updating files, empty resultset");
		return FALSE; 
	}
	
	foreach($search as $row) {

		if ($rename_to_old_scheme) {
		    $oldfile = $FilestoreDirectory."/".$row["fileuid"].".tmp";
		    $newfile = $FilestoreDirectory."/".$row["fileuid"].sanitizeFilename($row["fileoriginalname"]);
		} else {
		    $oldfile = $FilestoreDirectory."/".$row["fileuid"].sanitizeFilename($row["fileoriginalname"]);
		    $newfile = $FilestoreDirectory."/".$row["fileuid"].".tmp";
		}
		if (file_exists($oldfile)) {
		    $result = rename($oldfile, $newfile);
		    // check for error 
		    if (!$result) { 
		        logProcess("CONVERT","Error renaming file ". $oldfile . " to " . $newfile);
		    } else {
		        logProcess("CONVERT","Renamed " . $oldfile . " to " . $newfile);
		    }
		}
	}
	return true;
	}

function logProcess($client,$message)
	{
	global $config;
	
	if($config["debug"])
	{
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
?>
