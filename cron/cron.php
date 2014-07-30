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
date_default_timezone_set(Config::get('Default_TimeZone'));

// check if session already exists
if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 

// log that cron has started running
logProcess("CRON","Cron started");
if (cleanUp() && sendSummaryEmails()) {
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
	$db = DB::getInstance();
	
	// check log_location exists	
	if (!file_exists(Config::get('log_location'))) {
	logProcess("CRON","Unable to find log_location location specified in config.php  :".Config::get('log_location'));
	return false;
	}
	
	// check site_filestore exists
	if (!file_exists(Config::get('site_filestore'))) {
		logProcess("CRON","Unable to find site_filestore location specified in config.php  :".Config::get('site_filestore'));
		return false;
	}	
	
	// check site_temp_filestore exists
	if (!file_exists(Config::get('site_temp_filestore'))) {
		logProcess("CRON","Unable to find site_temp_filestore location specified in config.php  :".Config::get('site_temp_filestore'));
		return false;
	}	
	
	// remove any files with no uid - leftover from bug earlier beta that save files without uid's
	$sqlQuery = "DELETE FROM files WHERE fileuid IS NULL";
    $statement = $db->prepare($sqlQuery);
    $db->execute($statement);
	 
	$FilestoreDirectory = Config::get('site_filestore');

	//
	// Phase 1: check for any expired files and vouchers in the database first and close status in database
	//
	$today = date(Config::get('db_dateformat')); 

	// expired voucher is closed
    $statement = $db->prepare("UPDATE files SET filestatus = 'Voucher Cancelled' WHERE fileexpirydate < :fileexpirydate AND (filestatus = 'Voucher')");
    $statement->bindParam(':fileexpirydate', $today);
    $db->execute($statement);

	// expired file is deleted
    $statement = $db->prepare("UPDATE files SET filestatus = 'Deleted' WHERE fileexpirydate < :fileexpirydate AND (filestatus = 'Available')");
    $statement->bindParam(':fileexpirydate', $today);
    $db->execute($statement);

	// Phase 2: remove files on disk that do not have at least one Available file associated with it
	// in the database (loop through directory and check if file has status Available)

	// Open the folder
	$dir_handle = @opendir($FilestoreDirectory) or die("Unable to open $FilestoreDirectory"); 

	// First find Available fileuids in the database
	$statement = $db->prepare("SELECT fileuid FROM files WHERE filestatus = 'Available'");
    $statement = $db->execute($statement);
	$available_fileuids = $statement->fetchAll(PDO::FETCH_COLUMN);

	// Loop through the files in FilestoreDirectory 
	while ($filename = readdir($dir_handle)) {
	
		// skip . and ..
		if($filename == "." || $filename == "..") {
			continue;
		}
		
		if(strpos(Config::get('cron_exclude prefix'), substr($filename,0,1)) === 0) {
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
					if (!Config::get('cron_shred')) {
						// simply delete (unlink) the file
						unlink($FilestoreDirectory.$filename);
						logProcess("CRON","File Removed (Expired)".$FilestoreDirectory.$filename);    
					} else {
						// use gnu coreutils' shred to permanently remove the file from disk:
						system (Config::get('cron_shred_command') .' '. escapeshellarg($FilestoreDirectory.$filename), $retval);
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

    $statement= $db->prepare("SELECT * FROM files WHERE filestatus = 'Available'");
    $statement = $db->execute($statement);
    $search = $statement->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($search as $row) {

		// we don't use ensureSaneFileUid()/sanitizeFilename() here because file_exists()
		// is harmless, and the sanitized uid/filename might coincide with another file,
		// in which case this file would never get status = Closed.
		if (!file_exists($FilestoreDirectory."/".$row["fileuid"].".tmp")) {

			// change status to closed in database
            $statement = $db->prepare("UPDATE files SET filestatus = 'Deleted' WHERE fileid = :fileid");
            $statement->bindParam(':fileid', $row['fileid']);
            $db->execute($statement);

			logProcess("CRON","Removed (File not Available) ".$FilestoreDirectory."/".$row["fileuid"].".tmp");
		}
	}
	
	
	// Phase 4: remove files in temp folder older than days specified in Config::get('cleanuptempdays')
	
	$tempFilestoreDirectory = Config::get('site_temp_filestore');

	// Open the folder
	$dir_handle = @opendir($tempFilestoreDirectory) or die("Unable to open $tempFilestoreDirectory"); 

	// Loop through the files in tempFilestoreDirectory 
	while ($filename = readdir($dir_handle)) {
	
		// skip . and ..
		if($filename == "." || $filename == ".." || $filename == "index.html") {
			continue;
		}
		
		if(strpos(Config::get('cron_exclude prefix'), substr($filename,0,1)) === 0) {
			logProcess("CRON","Ignored file: " . $tempFilestoreDirectory.$filename);
			continue;
		}
			// number of seconds before cleanup of temp files from Config::get('cron_cleanuptempdays') or default 7 days (604800 seconds)
			$cron_cleanuptemptime =(Config::exists('cron_cleanuptempdays')) ? Config::get('cron_cleanuptempdays')*60*60*24 : 604800;
			if (is_file($tempFilestoreDirectory.$filename)) {
				// Don't remove the file if mtime is less then the configured cleanup time old
				if (time() - filemtime($tempFilestoreDirectory.$filename) < $cron_cleanuptemptime) {
					logProcess("CRON","Temp File NOT removed (last modification less then $cron_cleanuptemptime seconds ago)".$tempFilestoreDirectory.$filename);
				} else {
					// setting to allow for file wiping
					if (!Config::get('cron_shred')) {
						// simply delete (unlink) the file
						unlink($tempFilestoreDirectory.$filename);
						logProcess("CRON","Temp File Removed (Expired)".$tempFilestoreDirectory.$filename);    
					} else {
						// use gnu coreutils' shred to permanently remove the file from disk:
						system (Config::get('cron_shred_command') .' '. escapeshellarg($tempFilestoreDirectory.$filename), $retval);
						if ( $retval === 0 ) {
							logProcess("CRON","Temp File Shredded (Expired)".$tempFilestoreDirectory.$filename);
						} else {
							logProcess("CRON","Error ($retval) while shredding".$tempFilestoreDirectory.$filename);
						}
					}
				}
		}
	}
	
	// Close directory
	closedir($dir_handle);
	
	return true;
}

// Send emails with summaries of all activity on a user's transactions within the last 24 hours.
function sendSummaryEmails() {
    $db = DB::getInstance();
    $sendMail = Mail::getInstance();

    $last24h = date(Config::get('db_dateformat'), time() - 60 * 60 * 24);

    $statement = $db->prepare("SELECT logdate, logfilename, logfrom, logto, logfiletrackingcode, logtype FROM logs WHERE logdailysummary = 'true' AND logdate >= :logdate ORDER BY logdate DESC");
    $statement->bindParam(':logdate', $last24h);
    $statement = $db->execute($statement);
    $search = $statement->fetchAll(PDO::FETCH_NUM);

    // Turn query results into an array of transactions with associated activity logs.
    $transactions = array();

    foreach($search as $row) {
        $added = false;

        if ($row['logtype'] == 'Download') {
            // 'Download' log entries swap the recipient and uploader. Swap them back for this purpose.
            $tmp = $row['logfrom'];
            $row['logfrom'] = $row['logto'];
            $row['logto'] = $tmp;
        }

        foreach ($transactions as &$transaction) {
            // A transaction can be identified by the combination of tracking code and uploader email.
            if ($transaction[0]['logfrom'] == $row['logfrom'] && $transaction[0]['logfiletrackingcode'] == $row['logfiletrackingcode']) {
                $transaction[] = $row;
                $added = true;
                break;
            }
        }

        unset($transaction);

        if (!$added) {
            $transactions[][0] = $row;
        }
    }

    // Send an email for each of the transactions.
    foreach ($transactions as $transaction) {
        if (!$sendMail->sendSummary($transaction)) {
            return false;
        }
    }

    return true;
}

function logProcess($client,$message) {
	
	if(Config::get('debug'))
	{
		$dateref = date("Ymd");
		$data = date("Y/m/d H:i:s");
		$myFile = Config::get('log_location').$dateref."-".$client.".log.txt";
		$fh = fopen($myFile, 'a') or die("can't open file");
		// don't print errors on screen when there is no session.
		if(session_id()){
			$sessionId = session_id();
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
