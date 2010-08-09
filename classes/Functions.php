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
 
// ---------------------------------------
// Format bytes into readbable text format
function formatBytes($bytes, $precision = 2) {

	if($bytes >  0) 
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	return 0;
} 

// ---------------------------------------
// Create Unique ID for vouchers
 function getGUID() {

	return sprintf(
		'%08x-%04x-%04x-%02x%02x-%012x',
		mt_rand(),
		mt_rand(0, 65535),
		bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)),
		bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
		mt_rand(0, 255),
		mt_rand()
	);
}

//---------------------------------------
// Replace illegal chars with _ character in supplied filenames

function sanitizeFilename($filename){
	
	if (!empty($filename)) {
		$filename = preg_replace("/^\./", "_", $filename);
		return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
	} else {
		//trigger_error("invalid empty filename", E_USER_ERROR);
		return "";
	}
}

//---------------------------------------
// Error if fileUid doesn't look sane

function ensureSaneFileUid($fileuid){
	
	global $config;

	if (preg_match($config['voucherRegEx'], $fileuid) and strLen($fileuid) == $config['voucherUIDLength']) {
		return $fileuid;
	} else {
		trigger_error("invalid file uid $fileuid", E_USER_ERROR);
	}
}

class Functions {

	private static $instance = NULL;
	private $saveLog;
	private $db;
	private $CFG;
	private $sendmail;
	private $authsaml;
	
	// the following fields are returned without fileUID to stop unauthorised users accessing the fileUID
	public $returnFields = " fileid, fileexpirydate, fileto , filesubject, fileactivitydate, filemessage, filefrom, filesize, fileoriginalname, filestatus, fileip4address, fileip6address, filesendersname, filereceiversname, filevouchertype, fileauthuseruid, fileauthuseremail, filecreateddate, fileauthurl, fileuid ";	

	public function __construct() {
		
		$this->saveLog = Log::getInstance();
		$this->db = DB::getInstance();
		$this->CFG = config::getInstance();
		$this->sendmail = Mail::getInstance();
		$this->authsaml = AuthSaml::getInstance();
	}
	
	public static function getInstance() {
		// Check for both equality and type		
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	} 
	
	//---------------------------------------
	// Return Basic Database Statistics e.g. Up xx Gb (files xx) | Down xx Gb (files xx)
	public function getStats() {
	
		$config = $this->CFG->loadConfig();
		
		$statString = "| UP: ";
		$search =  $this->db->fquery("SELECT * FROM logs WHERE logtype='Uploaded'");
		
		if (!$search) {	$this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$total_records = pg_num_rows($search);
		$statString = $statString.$total_records." files ";
		$search = $this->db->fquery("SELECT SUM(logfilesize) as total_uploaded FROM logs WHERE logtype='Uploaded'");
		
		if (!$search) {	$this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$sRow = pg_fetch_array($search);
				
		$total = $sRow['total_uploaded'];
		$statString = $statString."(".round($total/1024/1024/1024)."GB) |" ;
		$search =  $this->db->fquery("SELECT * FROM logs WHERE logtype='Download'");
		
		if (!$search) {	$this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
				
		$total_records = pg_num_rows($search);
		$statString = $statString." DOWN: ".$total_records." files ";
		$search = $this->db->fquery("SELECT SUM(logfilesize) as total_downloaded FROM logs WHERE logtype='Download'");
		
		if (!$search) {	$this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$sRow = pg_fetch_array($search );
		
		$total = $sRow['total_downloaded'];
		$statString = $statString."(".round($total/1024/1024/1024)."GB) |";
		
		return $statString;
		
	}
	
	
	//---------------------------------------
	// Clean up missing files
	// Remove out of date files and vouchers
	public function cleanUp() 
	{
	
		$config = $this->CFG->loadConfig();
		
	if (!file_exists($config["log_location"])) {
	
	customError("", "Unable to find log_location location specified in config.php", $config["log_location"],"");
	return false;
	}
	
	if (!file_exists($config["site_filestore"])) {
		customError("", "Unable to find site_filestore location specified in config.php", $config["site_filestore"],"");
		return false;
	}	
	
	if (!file_exists($config["site_temp_filestore"])) {
	
		customError("", "Unable to find site_temp_filestore location specified in config.php", $config["site_temp_filestore"],"");
		return false;
	}	
	
	// remove any files with no uid - leftover from bug earlier beta that save files without uid's
	$sqlQuery = "
					DELETE FROM					 
						files 
					WHERE 
						fileuid IS NULL
				";
	
	$this->db->fquery($sqlQuery);
	
			 
	$FilestoreDirectory = $config["site_filestore"];

	// check for any expired files first and close status in database
	$today = date($config['postgresdateformat']); 
	
	// if file not closed and past expiry date then close the file
	$searchquery = "SELECT * FROM files WHERE  fileexpirydate < '%s' AND (filestatus = 'Available' or filestatus = 'Voucher')";
	$search = $this->db->fquery($searchquery, $today);
		
		// check for error in SQL
	if (!$search) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
	
	while($row = pg_fetch_assoc($search)) {
		
		// remove from database
		$query = "UPDATE Files SET filestatus = 'Closed' WHERE fileid='%s'";
		$result = $this->db->fquery($query, $row['fileid']);
		
		// check for error in SQL
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
	}

	// remove files that do not have at least one Available file associated with it
	// loop through directory and check file is Available
	
    // Open the folder
    $dir_handle = @opendir($FilestoreDirectory) or die("Unable to open $FilestoreDirectory"); 

    // Loop through the files
    while ($file = readdir($dir_handle)) {
	
	// skip . and ..
	if($file == "." || $file == "..")
	{
		continue;
	}
	
	// check filename in database
	$query = "SELECT * FROM files WHERE  fileuid = '%s' AND filestatus = 'Available'";
	$result = $this->db->fquery($query, substr($file,0,36));
	
	$total_results = pg_num_rows($result);
	if($total_results < 1) {
	// no Files Available match this file so delete the file
		
		if (file_exists($FilestoreDirectory.$file)) {
		unlink($FilestoreDirectory.$file);
		// log removal
		$this->saveLog->saveLog($result[0],"File Removed (Expired)",$FilestoreDirectory.$file);	
		}
	}
    }

    // Close
    closedir($dir_handle);

	// close all entries that do not have a pyhsical file in storage
	$search = $this->db->fquery("SELECT * FROM files WHERE filestatus = 'Available'"); 
		
		// check for error in SQL
		if (!$search) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
	
	while($row = pg_fetch_assoc($search)) {

		// we don't use ensureSaneFileUid()/sanitizeFilename() here because file_exists()
		// is harmless, and the sanitized uid/filename might coincide with another file,
		// in which case this file would never get status = Closed.
		if (!file_exists($FilestoreDirectory."/".$row["fileuid"].$row["fileoriginalname"])) {

		// change status to closed in database
		$query = "UPDATE Files SET filestatus = 'Closed' WHERE fileid='%s'";
		$this->saveLog->saveLog($row,"Removed (File not Available)","");	
		$result = $this->db->fquery($query, $row['fileid']);

		// check for error in SQL
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		}
		
	}
	return true;
	}
	
	//---------------------------------------
	// Get Splash Screen text for all users
	public function getSplash() {
	
		// return only splash data
		$config = $this->CFG->loadConfig();
			
		$flexconfig = array();
		$flexconfig['site_splashtext'] = $config['site_splashtext'];
		$flexconfig['site_name'] = $config['site_name'];
		$flexconfig['aboutURL'] = $config['aboutURL']; // 
		$flexconfig['helpURL'] = $config['helpURL']; //
		return json_encode($flexconfig);
		}
		
	//---------------------------------------
	// Retrun Specific config fields required by flex
	// Returns as JSON Array	
	public function getConfig() {
		$config = $this->CFG->loadConfig();
		$flexconfig = array();

		// set  configs
		$flexconfig['ban_extension'] = $config['ban_extension'];
		$flexconfig['admin'] = $config['admin'] ;
		$flexconfig['site_showStats'] = $config['site_showStats'];
		$flexconfig['versionNumber'] = $config['versionNumber'];
		$flexconfig['displayUserName'] = $config['displayUserName'];
		$flexconfig['site_splashtext'] = $config['site_splashtext'];
			
		$flexconfig['aboutURL'] = $config['aboutURL'];
		$flexconfig['helpURL'] = $config['helpURL'];
		
		$flexconfig['site_voucherinstructions'] = $config['site_voucherinstructions']; 
	
		//$flexconfig['max_file_size'] = $config['max_file_size'];  // depricated 23/3/2010
		$flexconfig['max_flash_upload_size'] = $config['max_flash_upload_size']; // 2GB
		$flexconfig['max_gears_upload_size'] = $config['max_gears_upload_size']; // 100 GB
		$flexconfig["debug"] = $config["debug"];
		$flexconfig['site_url'] = $config['site_url']; // URL to Filesender
		$flexconfig['site_simplesamlurl'] =$config['site_simplesamlurl'] ;
		$flexconfig['site_filestore'] = $config['site_filestore'];  			// use absolute locations
		$flexconfig['site_temp_filestore'] = $config['site_temp_filestore'] ;	// use absolute locations
		$flexconfig['site_downloadurl'] = $config['site_downloadurl'];
		$flexconfig['site_defaultlanguage'] = $config['site_defaultlanguage'] ;
		$flexconfig['site_name'] = $config['site_name'] ;
		$flexconfig['site_icon'] = $config['site_icon'];
		$flexconfig['default_daysvalid'] = $config['default_daysvalid'];
		$flexconfig['gearsURL'] = $config['gearsURL'];
		$flexconfig['datedisplayformat'] = $config['datedisplayformat'];
		$flexconfig['AuP_default'] = $config['AuP_default']; //Show and request approval for AuP
		$flexconfig['AuP'] = $config['AuP']; //Degault AuP value
		$flexconfig['AuP_label'] = $config["AuP_label"];
		$flexconfig['AuP_terms'] = $config["AuP_terms"];
		$flexconfig['help_link_visible'] = $config["help_link_visible"];	// if drivespace is low send email to admins
		$flexconfig['max_email_recipients'] = $config["max_email_recipients"];
	
		if(disk_free_space($config['site_filestore'])/disk_total_space($config['site_filestore']) * 100 < $config["server_drivespace_warning"] ) { 
		$this->saveLog->saveLog("","Drive Space Below ".$config["server_drivespace_warning"]."% ","");
		$this->sendmail->sendemailAdmin("Drive space is below ".$config["server_drivespace_warning"]."% on ".$config['site_url']." (".$config['site_filestore'].").");
		
		} 
	
		return json_encode($flexconfig);
	}
	
	//---------------------------------------
	// Get Voucher for a specified user based on eduPersonTargetedID
	public function getVouchers() {
	
		$config = $this->CFG->loadConfig();
		
		$dbCheck = DB_Input_Checks::getInstance();
		
				
		if( $this->authsaml->isAuth()) {
			$authAttributes = $this->authsaml->sAuth();
			} else {
			$authAttributes["eduPersonTargetedID"] = "";
			}
		
		
		$result = $this->db->fquery("SELECT %s FROM files WHERE (fileauthuseruid = '%s') AND filestatus = 'Voucher'",
						$this->returnFields, 
						$authAttributes["eduPersonTargetedID"]);
		
		// check for error in SQL
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$returnArray = array();
		while($row = pg_fetch_assoc($result))
		{
			 array_push($returnArray, $row);
		}
		echo json_encode($returnArray);
	}
		
	//---------------------------------------
	// Get Files for a specified user based on eduPersonTargetedID
	public function getUserFiles() {
	
		$config = $this->CFG->loadConfig();
		
		$dbCheck = DB_Input_Checks::getInstance();

		if( $this->authsaml->isAuth()) {
			$authAttributes = $this->authsaml->sAuth();
			} else {
			$authAttributes["eduPersonTargetedID"] = "nonvalue";
			}
		
		$result = $this->db->fquery("SELECT %s FROM files WHERE (fileauthuseruid = '%s') AND filestatus = 'Available'  ORDER BY fileactivitydate ASC",
						$this->returnFields, 
						$authAttributes["eduPersonTargetedID"]);
		
		// check for error in SQL
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$returnArray = array();
		while($row = pg_fetch_assoc($result))
		{
			 array_push($returnArray, $row);
		}
			echo json_encode($returnArray);
		}

	//---------------------------------------
	// Return logs if users is admin
	// current email authenticated as per config["admin"]
	public function adminLogs() {
	
	// check if this user has admin access before returning data

	if($this->authsaml->authIsAdmin()) { 

		
		$result = $this->db->fquery("SELECT logtype, logfrom , logto, logdate, logfilesize, logfilename, logmessage FROM logs ORDER BY logdate DESC");
		
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		
		$returnArray = array();
		while($row = pg_fetch_assoc($result))
		{
		 array_push($returnArray, $row);
		}
		echo json_encode($returnArray);
		}
		
	}

	//---------------------------------------
	// Return Files if users is admin
	// current email authenticated as per config["admin"]
	public function adminFiles() {
	
		// check if this user has admin access before returning data
		if($this->authsaml->authIsAdmin()) { 
	
		
		$result = $this->db->fquery("SELECT %s FROM files ORDER BY fileactivitydate DESC", $this->returnFields);
		if (!$result) { $this->saveLog->saveLog("","Error",pg_last_error()); return FALSE; }
		$returnArray = array();
		while($row = pg_fetch_assoc($result))
		{
			 array_push($returnArray, $row);
		}
			echo json_encode($returnArray);
		}
	}
	
	//---------------------------------------
	// Return file information based on filervoucheruid
	// 
	public function getFile($dataitem) {
	
		$config = $this->CFG->loadConfig();
		
		// check authentication as File UID is returned
		
		$vid = $dataitem['filevoucheruid'];
		
		$result = $this->db->fquery("SELECT * FROM files where filevoucherid = '%s'", $vid);
		
		if (!$result) { $this->saveLog->saveLog($dataitem,"Error",pg_last_error()); return FALSE; }
		
		$returnArray = array();
		while($row = pg_fetch_assoc($result)){
		    array_push($returnArray, $row);
		}
		return json_encode($returnArray);
	}
	
	//---------------------------------------
	// Return voucher information based on filervoucheruid
	// 
	public function getVoucher($vid) {
	
		$config = $this->CFG->loadConfig();
		
		// check authentication as file UID is returned

		$result = $this->db->fquery("SELECT * FROM files where fileid = '%s'", $vid);
		
		if (!$result) { $this->saveLog->saveLog($dataitem,"Error",pg_last_error()); return FALSE; }
		
		$returnArray = array();
		while($row = pg_fetch_assoc($result)){
		    array_push($returnArray, $row);
		}
		return $returnArray;
	}
	
	//---------------------------------------
	// Email and log when a file is downloaded
	// 
	public function downloadedFile() {
	
		$config = $this->CFG->loadConfig();
		
			
		$jsonString = rawurldecode($_POST['jsonSendData']);
		$jsonString = str_replace("\\", "", $jsonString);
		$data = json_decode($jsonString, true);
		$dataitem = $data[0];
		$tempEmail = $dataitem["fileto"];
		$dataitem["fileto"] = $dataitem["filefrom"];	
		$dataitem["filefrom"] = $tempEmail;
		$this->saveLog->saveLog($dataitem,"Download","");
		return $this->sendmail->sendEmail($dataitem,$config['filedownloadedemailbody']);
	
}


	//---------------------------------------
	// Insert new file or voucher
	// 
	public function insertFile(){

		$config = $this->CFG->loadConfig();
		$dbCheck = DB_Input_Checks::getInstance();

		$jsonString = rawurldecode($_POST['jsonSendData']);
		$jsonString = str_replace("\\", "", $jsonString);
		$data = json_decode($jsonString, true);
		$dataitem = $data[0];

		if( $this->authsaml->isAuth()) {
			$authAttributes = $this->authsaml->sAuth();
			$dataitem['fileauthuseruid'] = $authAttributes["eduPersonTargetedID"] ;
			$dataitem['fileauthuseremail'] = $authAttributes["email"];
		}

		$result = $this->db->fquery("
				INSERT INTO files (

					fileexpirydate,
					fileto,
					filesubject,
					fileactivitydate,
					filevoucheruid,
					filemessage,
					filefrom,
					filesize,
					fileoriginalname,
					filestatus,
					fileip4address,
					fileip6address,
					filesendersname,
					filereceiversname,
					filevouchertype,
					fileuid,
					fileauthuseruid,
					fileauthuseremail,
					filecreateddate

				) VALUES
				( '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",

					date($config['postgresdateformat'], strtotime($dataitem['fileexpirydate'])),
					$dataitem['fileto'],
					isset($dataitem['filesubject']) ? $dataitem['filesubject'] : "NULL",
					date($config['postgresdateformat'], time()),
					$dataitem['filevoucheruid'],
					isset($dataitem['filemessage']) ? $dataitem['filemessage'] : "NULL",
					$dataitem['filefrom'],
					$dataitem['filesize'],
					// inserted vouchers have no filenames, but inserted files must have a non-empty filename
					(isset($dataitem['filestatus']) && $dataitem['filestatus'] == "Voucher")
						? "NULL"
						: sanitizeFilename($dataitem['fileoriginalname']),
					$dataitem['filestatus'],
					$dbCheck->checkIp($_SERVER['REMOTE_ADDR']),
					$dataitem['fileip6address'],
					$dataitem['filesendersname'],
					$dataitem['filereceiversname'],
					$dataitem['filevouchertype'],
					ensureSaneFileUid($dataitem['fileuid']),
					$dataitem['fileauthuseruid'],
					$dataitem["fileauthuseremail"],
					date($config['postgresdateformat'], time())
		);

		if (!$result) { $this->saveLog->saveLog($dataitem,"Error",pg_last_error()); return FALSE; }

		if($dataitem['filestatus'] == "Voucher") {
			$this->saveLog->saveLog($dataitem,"Voucher Sent","");
			return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
		} else {
			$this->saveLog->saveLog($dataitem,"Uploaded","");
			return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
		}
	}


	//---------------------------------------
	// Update file or voucher
	// 	
	public function updateFile(){

		$config = $this->CFG->loadConfig();
			
		$dbCheck = DB_Input_Checks::getInstance();
		
		$jsonString = rawurldecode($_POST['jsonSendData']);
		$jsonString = str_replace("\\", "", $jsonString);
		$data = json_decode($jsonString, true);
		$dataitem = $data[0];
		
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$fileexpirydate 	=  date($config['postgresdateformat'],strtotime($dataitem['fileexpirydate']));
		$fileto			=  $dataitem['fileto'];
		
		if(isset($dataitem['filesubject'])){
			$filesubject 	= $dataitem['filesubject'];
		} else {
			$filesubject 	= "NULL";
		}
		$fileactivitydate 	= date($config['postgresdateformat'],time());//dateConversion($dbCheck->checkString($dataitem['fileactivitydate']));
		$filevoucheruid 	= $dataitem['filevoucheruid'];

		if(isset($dataitem['filemessage'])){
			$filemessage 	= $dataitem['filemessage'];
		} else {
			$filemessage 	= "NULL";
		}
		$filefrom 		= $dataitem['filefrom'];
		$filesize 		= $dataitem['filesize'];
		$fileoriginalname 	= sanitizeFilename($dataitem['fileoriginalname']);
	 	$filestatus 		= $dataitem['filestatus'];
		$fileip4address 	= $dbCheck->checkIp($ip);
	 	$fileip6address 	= "NULL";// stringconversion(pg_escape_string($dataitem['fileip6address']));
		$filesendersname 	= "NULL";//stringconversion(pg_escape_string($dataitem['filesendersname']));
		$filereceiversname 	= "NULL";//  stringconversion(pg_escape_string($dataitem['filereceiversname']));
		$filevouchertype 	= "NULL";// stringconversion(pg_escape_string($dataitem['filevouchertype']));
		$fileuid 		= ensureSaneFileUid($dataitem['fileuid']);
		$fileauthuseruid 	= $dataitem["fileauthuseruid"];
		$fileauthuseremail 	= $dataitem["fileauthuseremail"];
		$filecreateddate 	= date($config['postgresdateformat'],strtotime($dataitem['filecreateddate']));
		
		if (isset($dataitem['fileid'])){
			$fileid =   $dataitem['fileid'];
			$sqlQuery = "
						UPDATE 
							files 
						SET 
							fileexpirydate 		= '%s', 
							fileto 			= '%s', 
							filesubject 		= '%s', 
							fileactivitydate 	= '%s', 
							filevoucheruid 		= '%s', 
							filemessage		= '%s', 
							filefrom 		= '%s', 
							filesize 		= '%s', 
							fileoriginalname 	= '%s', 
							filestatus 		= '%s', 
							fileip4address		= '%s', 
							fileip6address 		= '%s', 
							filesendersname 	= '%s', 
							filereceiversname 	= '%s', 
							filevouchertype		= '%s', 
							fileuid 		= '%s',
							fileauthuseruid 	= '%s',
							fileauthuseremail 	= '%s',
							filecreateddate 	= '%s'  
						WHERE 
							fileid 			= %d";
			
			$result = $this->db->fquery($sqlQuery,
								$fileexpirydate,
								$fileto,
								$filesubject,
								$fileactivitydate,
								$filevoucheruid,
								$filemessage,
								$filefrom,
								$filesize,
								$fileoriginalname,
								$filestatus,
								$fileip4address,
								$fileip6address,
								$filesendersname,
								$filereceiversname,
								$filevouchertype,
								$fileuid,
								$fileauthuseruid,
								$fileauthuseremail,
								$filecreateddate,
								$fileid
								) or die("Error");
								
			

		if (!$result) { $this->saveLog->saveLog($dataitem,"Error",pg_last_error()); return FALSE; }
	
		$this->saveLog->saveLog($dataitem,"Updated","Using Voucher");
		return true;//sendmail->sendEmail($dataitem,$config['defaultmailsendbody']);
		
		}	
	}	
	//---------------------------------------
	// Delete a voucher
	// 
	public function deleteVoucher($fileid){
	
			$config = $this->CFG->loadConfig();
	
			// check authentication SAML User
			if( $this->authsaml->isAuth()) {
			
			$dbCheck = DB_Input_Checks::getInstance();	
	
			$sqlQuery = "
					UPDATE 
						files 
					SET 
						filestatus = 'Voucher Cancelled' 
					WHERE 
						fileid = %d
				";
	
			$this->db->fquery($sqlQuery, $fileid);
	
			$fileArray =  $this->getVoucher($fileid);
			
			$this->sendmail->sendEmail($fileArray[0],$config['defaultvouchercancelled']);	
			$this->saveLog->saveLog($fileArray[0],"Voucher Cancelled","");
	
			return true;
		} else {
			return false;
		}	
	}

	//---------------------------------------
	// Return filesize as integer from php
	// Function also habdles windows servers
	// 
	public function getFileSize($filename){


		$config = $this->CFG->loadConfig();
		
	if($filename == "" ) {
		return;
	} else {
		$file = $config["site_temp_filestore"].sanitizeFilename($filename);
	
		if (file_exists($file)) {
			if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
			{
				$size = trim(shell_exec("stat -c%s ". escapeshellarg($file)));
			} else { 
			   	$fsobj = new COM("Scripting.FileSystemObject"); 
				$f = $fsobj->GetFile($file); 
				$size = $f->Size; 
			}
				return $size;
			} else { 
				return 0;
			} 
		}
	}
	
	//---------------------------------------
	// Get drive space
	// Returns JSON array
	public function driveSpace() {
	
	$config = $this->CFG->loadConfig();
	
	$result["site_filestore_total"] = disk_total_space($config['site_filestore']);   			// use absolute locations result in bytes
	$result["site_temp_filestore_total"] = disk_total_space($config['site_temp_filestore']);   			// use absolute locations
	$result["site_filestore_free"] = disk_free_space($config['site_filestore']);   			// use absolute locations
	$result["site_temp_filestore_free"] = disk_free_space($config['site_temp_filestore']);   			// use absolute locations

	return json_encode($result);
	
	}
	
	//---------------------------------------
	// Move the file
	// move file from tmp directory to live directory and rename with Unique ID
	public function moveFile(){
		
		$config = $this->CFG->loadConfig();
	
		$jsonString = rawurldecode($_POST['jsonSendData']);
		$jsonString = str_replace("\\", "", $jsonString);
		$fileobj = json_decode($jsonString, true);
		$dataitem = $fileobj[0];
	
		if( $dataitem['fileoriginalname'] == "" || $dataitem['fileuid'] == "" ) {
			return false;
		} else {
			$filename = $config["site_temp_filestore"].sanitizeFilename($dataitem['fileoriginalname']);
			$newlocation = $config["site_filestore"].ensureSaneFileUid($dataitem['fileuid']).sanitizeFilename($dataitem['fileoriginalname']);
			if (file_exists( $filename)) {
				// move it
				rename($filename, $newlocation);
				$this->saveLog->saveLog($dataitem,"File Moved","");
				return true;
			} else {
			customError("", "Unable to move file ".$filename." to ".$newlocation, "","");
			$this->saveLog->saveLog($dataitem,"Error","Cannot Move file from Temp Folder");
			return false;
				
			}
		}
	
	}
	
}
	
?>
