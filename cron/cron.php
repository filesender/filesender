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
// this file is called by CRON to remove files that have expired and close the expired files in the database
// ---------------------------------

 
// required as this page is called from CRON not from a web browser
chdir(dirname(__FILE__));

// force all error reporting
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL);
}

$filesenderbase = dirname(dirname(__FILE__));

// include all required classes
require_once("$filesenderbase/config/config.php");

$CFG = config::getInstance();
$config = $CFG->loadConfig();

require_once("$filesenderbase/includes/ErrorHandler.php");
require_once("$filesenderbase/classes/DB.php");
require_once("$filesenderbase/classes/Mail.php");
require_once("$filesenderbase/classes/DB_Input_Checks.php");
require_once("$filesenderbase/classes/Log.php");

// set cron variable to force
$cron = true;

$sendmail = Mail::getInstance();

// set time zone for this session
date_default_timezone_set($config['Default_TimeZone']);

// check if session already exists
if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 

// log that cron has started running
logProcess("CRON","Cron started");
if (cleanUp()) {
	// cron completed - log
	logProcess("CRON","Cron Complete");
} else {
	// email admin - error in Cron
	logProcess("CRON","Cron Error - check error log");
}



//---------------------------------------
	// Clean up missing files
	// Remove out of date files and vouchers
function cleanUp() {
	
	global $config;
	$db = DB::getInstance();
	
	// check log_location exists	
	if (!file_exists($config["log_location"])) {
	logProcess("CRON","Unable to find log_location location specified in config.php  :".$config["log_location"]);
	return false;
	}
	
	// check site_filestore exists
	if (!file_exists($config["site_filestore"])) {
		logProcess("CRON","Unable to find site_filestore location specified in config.php  :".$config["site_filestore"]);
		return false;
	}	
	
	// remove any files with no uid - leftover from bug earlier beta that save files without uid's
	$sqlQuery = "DELETE FROM files WHERE fileuid IS NULL";
	$db->fquery($sqlQuery);
	
	 
	$FilestoreDirectory = $config["site_filestore"];

	//
	// Phase 1: check for any expired files and vouchers in the database first and close status in database
	//
	$today = date($config['db_dateformat']); 
	
	// if file not closed and past expiry date then close the file
	$searchquery = "SELECT * FROM files WHERE fileexpirydate < %s AND (filestatus = 'Available' OR filestatus = 'Voucher')";
	try {
		$search = $db->fquery($searchquery, $today);
	} catch (DBException $e) {
		logProcess("CRON","SQL Error on selecting files". $e->getMessage());
		return FALSE;
	}
		// expired voucher is closed
	try {
		$query = "UPDATE files SET filestatus = 'Voucher Cancelled' WHERE fileexpirydate < %s AND
			(filestatus = 'Voucher')";
		$db->fquery($query, $today);
	} catch (DBException $e) {
		logProcess("CRON", "SQL error while trying to change status to closed in expired vouchers" . $e->getMesssage());
		return FALSE;
	}
	// expired file is deleted
	try {
		$query = "UPDATE files SET filestatus = 'Deleted' WHERE fileexpirydate < %s AND
			(filestatus = 'Available')";
		$db->fquery($query, $today);
	} catch (DBException $e) {
		logProcess("CRON", "SQL error while trying to change status to deleted in expired files" . $e->getMesssage());
		return FALSE;
	}
	// Phase 2: remove files on disk that do not have at least one Available file associated with it
	// in the database (loop through directory and check if file has status Available)

	// Open the folder
	$dir_handle = @opendir($FilestoreDirectory) or die("Unable to open $FilestoreDirectory"); 

	// First find Available fileuids in the database
	$result = $db->fquery("SELECT fileuid FROM files WHERE filestatus = 'Available'");
	$available_fileuids = $result->fetchAll(PDO::FETCH_COLUMN);

	// Loop through the files in FilestoreDirectory 
	while ($filename = readdir($dir_handle)) {
	
		// skip . and ..
		if($filename == "." || $filename == "..") {
			continue;
		}
		
		if(strpos($config['cron_exclude prefix'], substr($filename,0,1)) === 0) {
			logProcess("CRON","Ignored file: " . $FilestoreDirectory.$filename);
			continue;
		}

		//check in list of Available files
		if(!in_array(substr($filename,0,36), $available_fileuids)) {
		// no Files Available match this file so delete the file
			if (is_file($FilestoreDirectory.$filename)) {
				// Don't remove the file if mtime is less then 24 hours (86400 seconds) old
				if (time() - filemtime($FilestoreDirectory.$filename) < 86400) {
					logProcess("CRON","File NOT removed (last modification less then 24 hours ago)".$FilestoreDirectory.$filename);
				} else {
					// setting to allow for file wiping
					if ( empty($config['cron_shred']) ) {
						// simply delete (unlink) the file
						unlink($FilestoreDirectory.$filename);
						logProcess("CRON","File Removed (Expired)".$FilestoreDirectory.$filename);    
					} else {
						// use gnu coreutils' shred to permanently remove the file from disk:
						system ($config['cron_shred_command'] .' '. escapeshellarg($FilestoreDirectory.$filename), $retval);
						if ( $retval === 0 ) {
							logProcess("CRON","File Shredded (Expired)".$FilestoreDirectory.$filename);
						} else {
							logProcess("CRON","Error ($retval) while shredding".$FilestoreDirectory.$filename);
						}
					}
				}
			}
		}
	}
	// Close directory
	closedir($dir_handle);
	
	// Phase 3:
	// Final cleanup is to close any records in the database that do not have a physical file attached to them
	// close all entries that do not have a pyhsical file in storage
	// We also check on the expiry date, so that files that are currently being uploaded and have "stale" records are left alone
	
	try {
		$search = $db->fquery("SELECT * FROM files WHERE filestatus = 'Available'"); 
	} catch	(DBException $e) {
		logProcess("CRON","SQL Error on updating files".$e->getMessage());
		return FALSE;		
	}

	
	foreach($search as $row) {

		// we don't use ensureSaneFileUid()/sanitizeFilename() here because file_exists()
		// is harmless, and the sanitized uid/filename might coincide with another file,
		// in which case this file would never get status = Closed.
		if (!file_exists($FilestoreDirectory."/".$row["fileuid"].".tmp")) {

			// change status to closed in database
			try {
				$query = "UPDATE files SET filestatus = 'Deleted' WHERE fileid = %s";
				$db->fquery($query, $row['fileid']);
			} catch (Exception $e) {
				logProcess("CRON","SQL Error Updating files ".$e->getMessage());
				return FALSE;
			}

			logProcess("CRON","Removed (File not Available) ".$FilestoreDirectory."/".$row["fileuid"].".tmp");
		}
	}
	return true;
}

function logProcess($client,$message) {
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
