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
 
chdir(dirname(__FILE__));

error_reporting(E_ALL);

$filesenderbase = dirname(dirname(__FILE__));

require_once("$filesenderbase/config/config.php");
require_once("$filesenderbase/classes/DB.php");
require_once("$filesenderbase/classes/EN_AU.php");
require_once("$filesenderbase/classes/Mail.php");
require_once("$filesenderbase/classes/DB_Input_Checks.php");

$lang = EN_AU::getInstance();
$CFG = config::getInstance();
$config = $CFG->loadConfig();
$sendmail = Mail::getInstance();

// set time zone for this session
date_default_timezone_set($config['Default_TimeZone']);

if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 

logProcess("CRON","Cron started");
if (cleanUp())
{
	// cron completed - log
	logProcess("CRON","Cron Complete");
} 
	else 
{
	// email admin - error in Cron
	logProcess("CRON","Cron Error - check error log");
}



//---------------------------------------
	// Clean up missing files
	// Remove out of date files and vouchers
function cleanUp() 
	{
	
	global $config;
	$db = DB::getInstance();
		
	if (!file_exists($config["log_location"])) {
	logProcess("CRON","Unable to find log_location location specified in config.php  :".$config["log_location"]);
	return false;
	}
	
	if (!file_exists($config["site_filestore"])) {
		logProcess("CRON","Unable to find site_filestore location specified in config.php  :".$config["site_filestore"]);
		return false;
	}	
	
	if (!file_exists($config["site_temp_filestore"])) {
		logProcess("CRON","Unable to find site_temp_filestore location specified in config.php  :".$config["site_temp_filestore"]);
		return false;
	}	
	
	// remove any files with no uid - leftover from bug earlier beta that save files without uid's
	$sqlQuery = "
					DELETE FROM					 
						files 
					WHERE 
						fileuid IS NULL
				";
	
	$db->fquery($sqlQuery);
	
			 
	$FilestoreDirectory = $config["site_filestore"];

	// check for any expired files first and close status in database
	$today = date($config['postgresdateformat']); 
	
	// if file not closed and past expiry date then close the file
	$searchquery = "SELECT * FROM files WHERE  fileexpirydate < '%s' AND (filestatus = 'Available' or filestatus = 'Voucher')";
	$search = $db->fquery($searchquery, $today);
		
		// check for error in SQL
	if (!$search) { 
		logProcess("CRON","SQL Error on selecting files".pg_last_error());
		return FALSE; 
		}
	
	while($row = pg_fetch_assoc($search)) {
		
		// remove from database
		$query = "UPDATE Files SET filestatus = 'Closed' WHERE fileid='%s'";
		$result = $db->fquery($query, $row['fileid']);
		
		// check for error in SQL
		if (!$result) { 
			logProcess("CRON","SQL Error on updating files".pg_last_error());
			return FALSE; 
		}
		
	}

	// remove files that do not have at least one Available file associated with it
	// loop through directory and check file is Available
	
    // Open the folder
    $dir_handle = @opendir($FilestoreDirectory) or die("Unable to open $FilestoreDirectory"); 

    // Loop through the files
    while ($file = readdir($dir_handle)) {
	
	// skip . and ..
	if($file == "." || $file == ".." || strpos($config['cron_exclude prefix'],substr($file,0,1)) === 0)
	{
		logProcess("CRON","Ignored file: ".$FilestoreDirectory.$file);
		continue;
	}
	
	
	// check filename in database
	$query = "SELECT * FROM files WHERE  fileuid = '%s' AND filestatus = 'Available'";
	$result = $db->fquery($query, substr($file,0,36));
	
	$total_results = pg_num_rows($result);
	if($total_results < 1) {
	// no Files Available match this file so delete the file
	
		if (is_file($FilestoreDirectory.$file) && file_exists($FilestoreDirectory.$file)) {
		unlink($FilestoreDirectory.$file);
		// log removal
		logProcess("CRON","File Removed (Expired)".$FilestoreDirectory.$file);	
		}
	}
    }

    // Close
    closedir($dir_handle);

	// close all entries that do not have a pyhsical file in storage
	$search = $db->fquery("SELECT * FROM files WHERE filestatus = 'Available'"); 
		
		// check for error in SQL
		if (!$search) { 
		logProcess("CRON","SQL Error on updating files".pg_last_error());
		return FALSE; 
		}
	
	while($row = pg_fetch_assoc($search)) {

		// we don't use ensureSaneFileUid()/sanitizeFilename() here because file_exists()
		// is harmless, and the sanitized uid/filename might coincide with another file,
		// in which case this file would never get status = Closed.
		if (!file_exists($FilestoreDirectory."/".$row["fileuid"].$row["fileoriginalname"])) {

		// change status to closed in database
		$query = "UPDATE Files SET filestatus = 'Closed' WHERE fileid='%s'";
		
		$result = $db->fquery($query, $row['fileid']);
		
		// check for error in SQL
		if (!$result) { 
			logProcess("CRON","SQL Error Updating files ".pg_last_error());
			return FALSE; 
		}
		
		logProcess("CRON","Removed (File not Available) ".$FilestoreDirectory."/".$row["fileuid"].$row["fileoriginalname"]);
			
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
		$myFile = $config['log_location'].$dateref."-".$client."-error.log.txt";
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
