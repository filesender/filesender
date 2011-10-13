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

// ---------------------------------------
// Format bytes into readbable text format
function formatBytes($bytes, $precision = 2) {

    if($bytes >  0) 
    {
        $units = array(' Bytes', ' KB', ' MB', ' GB', ' TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . '' . $units[$pow];
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
        //return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
        return $filename;
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
    private $sendmail;
    private $authsaml;
    private $authvoucher;

    // the following fields are returned without fileUID to stop unauthorised users accessing the fileUID
    public $returnFields = " fileid, fileexpirydate, fileto , filesubject, fileactivitydate, filemessage, filefrom, filesize, fileoriginalname, filestatus, fileip4address, fileip6address, filesendersname, filereceiversname, filevouchertype, fileauthuseruid, fileauthuseremail, filecreateddate, fileauthurl, fileuid, filevoucheruid ";	

    public function __construct() {

        $this->saveLog = Log::getInstance();
        $this->db = DBAL::getInstance();
        $this->sendmail = Mail::getInstance();
        $this->authsaml = AuthSaml::getInstance();
        $this->authvoucher = AuthVoucher::getInstance();
    }

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 

	//Set the scratch message
	public function setScratchMessage($message) {
		$_SESSION['scratch'] = $message;
	}

	//Append to the scratch message
	public function appendScratchMessage($message) {
		logEntry("In appendScratchMessage");
		session_start();
		logEntry("session_start called");
		if(! array_key_exists('scratch',$_SESSION)) {session_register("scratch");}
		logEntry("Key didn't exist, registered");
		$_SESSION['scratch'] = $_SESSION['scratch'] .'<br/>' .$message;
		logEntry("Message added");
	}	
	
	public function getScratchMessage() {
		logEntry('in getScratchMessage');
		if(array_key_exists('scratch',$_SESSION)) {
			logEntry('returning scratch message'. $_SESSION['scratch']);
			return $_SESSION['scratch'];
		} else {return '';}
	}
	
	//Validate to: email addresses
	public function validate_to()
	{	
		global $config;
		logEntry("In validate_to");
		$emailto = str_replace(",",";",$_POST["fileto"]);
		$emailArray = preg_split("/;/", $emailto);
		logEntry("emailArray splitted");
		//To many recipients - err out
		if(count($emailArray) > $config['max_email_recipients'] ) {return false;}
		logEntry("emailArray not to long (not to any recipients)");
		
		//Check if we have valid email adddresses
		//We do have well thought out regular expressions, but I saw this one which seems to work and is maintained...
		//Anyway, easy to change if we want the regex in.
		foreach($emailArray as $email) {
			logEntry("in loop");
			if(filter_var($email,FILTER_VALIDATE_EMAIL)) {continue;}
			else {
				logEntry("no match for ".$email." returning false");
	            return false; 
	        }
		}
		logEntry("emails ok");
		return true;
	}
	
	//Validate date
	public function validate_date()
	{
		global $config;
		//date does a clever thing: if a value for a day or month is too high, it does a "right shift and prepends a 0
		//E.g. day 33 -> 03 This protects us against nn-existing dates
		$_POST['fileexpirydate'] = (date($config['datedisplayformat'],strtotime($_POST["fileexpirydate"])));
		logEntry("Expiry date now is ". $_POST['fileexpirydate']);
		return true;
	}
	
	//Validate AUP
	public function validate_aup()
	{
		return isset($_POST["aup"]);
	}

	
	// validate extension for banned file names
	public function validate_extension() {
		global $config;
		$outcome = true;
		foreach( $_POST as $key => $value) {
			logEntry("POST parameter " . $key . " = " .$value);
		}
		$lastelem = array_pop(explode(".",$_POST['n']));
		logEntry('Last element = ' . $lastelem);
		$banned = explode(",",$config['ban_extension']);
		foreach($banned as $key => $naughty) {
			if($lastelem == $naughty) {$outcome = false; break;}
		}
		logEntry('Outcome = ' . $outcome);
		return $outcome;
	}

	//Validate if the file is not 0 bytes long
	public function validate_zero_filesize()
	{	
		logEntry("Is numeric ". is_numeric($_POST['total']));
		if (isset($_POST["total"])) {return (! $_POST["total"] == 0);}
	}
	
	
	public function validatePlainUpload() {
		
		global $config;
		
		lang("_INVALID_MISSING_EMAIL");
		
		logEntry("in validatePlainUpload");
		$all_good = true;
		logEntry("0All good in validation = ".$all_good);
		
		if(! $this->validate_to()) { 
			logEntry("0-1 All wrong in email validation = ".$all_good);
			
			$all_good = false;
			$this->appendScratchMessage(lang("_INVALID_MISSING_EMAIL"));
			logEntry("0-2 All wrong in email validation = appended message");
			
		}
		logEntry("1 All still good in validation? = ".$all_good);
		if(! $this->validate_date()) { 
			logEntry("1-A All wrong in validation date = ".$all_good);
			$all_good = false;
			$this->appendScratchMessage(lang("_INVALID_DATE_FORMAT"));
			logEntry("1-b All wrong in validation date = ".$all_good);
		}
		logEntry("2 All still good in validation? = ".$all_good);
		if(! $this->validate_aup()) { 
			logEntry("2-A All wrong in validation AuP = ".$all_good);
			$all_good = false;
			logEntry("all_good = ".$all_good);
			$this->appendScratchMessage(lang("_AGREETOC"));
			logEntry("Scratch ".lang("_AGREETOC"). " added.");
		}
		logEntry("3 All still good in validation ? = ".$all_good);
		if(! $this->validate_extension()) { 
			logEntry("3-A All wrong in validation extensions = ".$all_good);
			$all_good = false;
			$this->appendScratchMessage(lang("_INVALID_FILE_EXT"));
		}
		logEntry("4 All still good in validation? = ".$all_good);
		if(! $this->validate_zero_filesize()) { 
			logEntry("4-A All wrong in validation zero filesize = ".$all_good);
			$all_good = false;
			$this->appendScratchMessage(lang("_INVALID_FILESIZE_ZERO"));
		}
		
		logEntry("5 All still good in validation = ".$all_good);
		print_r($all_good);
		//If somethings is wrong, redirect with message in the scratch space
		if(! $all_good) {
			logEntry('Redirecting to page with scratch');
			header("Location: " . $config['site_url'].'index.php');
			exit;
		}
	}


    //---------------------------------------
    // Return Basic Database Statistics e.g. Up xx Gb (files xx) | Down xx Gb (files xx)
    public function getStats() {

        global $config;

        $statString = "| UP: ";
        try {
        	$search =  $this->db->query("SELECT * FROM logs WHERE logtype='Uploaded'");
        } catch (DBALException $e) {
        	$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE; 
        }

        $total_records = sizeof($search);
        $statString = $statString.$total_records." files ";
        
		try {
			$search = $this->db->query("SELECT SUM(logfilesize) as total_uploaded FROM logs WHERE logtype='Uploaded'");
		} catch (Exception $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE; 
		}

        $total = $search[0]['total_uploaded'];
        $statString = $statString."(".round($total/1024/1024/1024)."GB) |" ;

		try {
			$search =  $this->db->query("SELECT * FROM logs WHERE logtype='Download'");
		} catch (Exception $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE; 	
		}

        $total_records = sizeof($search);
        $statString = $statString." DOWN: ".$total_records." files ";

		try {
        	$search = $this->db->query("SELECT SUM(logfilesize) as total_downloaded FROM logs WHERE logtype='Download'");
		} catch(DBALException $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE; 		
		}

        $total = $search[0]['total_downloaded'];
        $statString = $statString."(".round($total/1024/1024/1024)."GB) |";

        return $statString;

    }


    //---------------------------------------
    // Get Splash Screen text for all users
    public function getSplash() {

        // return only splash data
        global $config;

        $flexconfig = array();
        $flexconfig['site_splashtext'] = $config['site_splashtext'];
        $flexconfig['site_name'] = $config['site_name'];
        $flexconfig['aboutURL'] = $config['aboutURL']; // 
        $flexconfig['helpURL'] = $config['helpURL']; //
        $flexconfig['gearsURL'] = $config['gearsURL'];
        $flexconfig["debug"] = $config["debug"];
        $flexconfig["client_specific_logging"] = $config["client_specific_logging"]; // client looging true/false
        $flexconfig["client_specific_logging_uids"] = $config["client_specific_logging_uids"]; // "" is log all clients, or log for specif userid's or voucheruid's
        return json_encode($flexconfig);
    }

    //---------------------------------------
    // Retrun Specific config fields required by flex
    // Returns as JSON Array	
    public function getConfig() {
		
        global $config;
        
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
		$flexconfig['emailRegEx'] = $config['emailRegEx'];
	

        // check file locations are correct
        if (!file_exists($config["log_location"])) {
            trigger_error("Unable to find log_location location specified in config.php  :".$config["log_location"], E_USER_ERROR);
            return false;
        }
        if (!is_writable($config["log_location"])) {
            trigger_error("Unable to write to log file location specified in config.php  :".$config["log_location"], E_USER_ERROR);
            return false;
        }
        if (!file_exists($config["site_filestore"])) {
            trigger_error("Unable to find site_filestore location specified in config.php  :".$config["site_filestore"], E_USER_ERROR);
            return false;
        }	
        if (!is_writable($config["site_filestore"])) {
            trigger_error("Unable to write to site_filestore location specified in config.php  :".$config["site_filestore"], E_USER_ERROR);
            return false;
        }	
        if (!file_exists($config["site_temp_filestore"])) {
            trigger_error("Unable to find site_temp_filestore location specified in config.php  :".$config["site_temp_filestore"], E_USER_ERROR);
            return false;
        }	
        if (!is_writable($config["site_temp_filestore"])) {
            trigger_error("Unable to write to site_temp_filestore location specified in config.php  :".$config["site_temp_filestore"], E_USER_ERROR);
            return false;
        }	
        if(disk_free_space($config['site_filestore'])/disk_total_space($config['site_filestore']) * 100 < $config["server_drivespace_warning"] ) { 
            $this->saveLog->saveLog("","Drive Space Below ".$config["server_drivespace_warning"]."% ","");
            $this->sendmail->sendemailAdmin("Drive space is below ".$config["server_drivespace_warning"]."% on ".$config['site_url']." (".$config['site_filestore'].").");

        } 

        return json_encode($flexconfig);
    }

    //---------------------------------------
    // Get Voucher for a specified user based on saml_uid_attribute
    public function getVouchers() {

       global $config;

        $dbCheck = DB_Input_Checks::getInstance();


        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "";
        }

        try {
        	$result = $this->db->query("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = '%s') AND filestatus = 'Voucher' ORDER BY fileactivitydate DESC",$authAttributes["saml_uid_attribute"]);
        } catch (DBALException $e) {
        	$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;
        }



        $returnArray = array();
        foreach($result as $row)
        {
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //---------------------------------------
    // Get Files for a specified user based on saml_uid_attribute
    public function getUserFiles() {

        global $config;

        $dbCheck = DB_Input_Checks::getInstance();

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "nonvalue";
        }
		try {
			$result = $this->db->query("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = '%s') AND filestatus = 'Available'  ORDER BY fileactivitydate DESC", $authAttributes["saml_uid_attribute"]);	
		} catch (DBALException $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;
		}
        
        $returnArray = array();
        foreach($result as $row )
        {
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //---------------------------------------
    // Return logs if users is admin
    // current email authenticated as per config["admin"]
    public function adminLogs($type) {

        // check if this user has admin access before returning data
		global $page;
		global $total_pages;
		$pagination = "";
		$maxitems_perpage = 50;
		if(isset($_REQUEST["page"]))
		{
		
		$result = $this->db->query("SELECT logtype FROM logs WHERE logtype = '$type'");
		$total = count($result );
		
		$total_pages[$type] = ceil($total/$maxitems_perpage);
		$page = intval($_REQUEST["page"]); 
  		if (0 == $page){
  		$page = 1;
  		}  
  		$start = $maxitems_perpage * ($page - 1);
  		$max = $maxitems_perpage;
		$pagination = "LIMIT ".$maxitems_perpage." OFFSET ".$start;
		} else {
		$pagination = "LIMIT ".$maxitems_perpage." OFFSET 0";
		}
        if($this->authsaml->authIsAdmin()) { 


            try {
            	$result = $this->db->query("SELECT logtype, logfrom , logto, logdate, logfilesize, logfilename, logmessage FROM logs WHERE logtype = '$type' ORDER BY logdate DESC ".$pagination);
            } catch (Exception $e) {
            	$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;	
            }

            $returnArray = array();
            foreach($result as $row) 
            {
                array_push($returnArray, $row);
            }
            return $returnArray;
        }

    }

    //---------------------------------------
    // Return Files if users is admin
    // current email authenticated as per config["admin"]
    public function adminFiles($type) {

		global $page;
		global $total_pages;
		$pagination = "";
		$maxitems_perpage = 50;
		if(isset($_REQUEST["page"]))
		{
		$result = $this->db->query("SELECT fileid FROM files WHERE filestatus = '$type'");
		$total = count($result);
		//echo $total;
  		$total_pages[$type] = ceil($total/$maxitems_perpage);
  		$page = intval($_REQUEST["page"]); 
  		if (0 == $page){
  		$page = 1;
  		}  
  		$start = $maxitems_perpage * ($page - 1);
  		$max = $maxitems_perpage;
		$pagination = "LIMIT ".$maxitems_perpage." OFFSET ".$start;
		} else {
			$pagination = "LIMIT ".$maxitems_perpage." OFFSET 0";
		}
		
        // check if this user has admin access before returning data
        if($this->authsaml->authIsAdmin()) { 


            try {
            	$result = $this->db->query("SELECT %s FROM files WHERE filestatus = '$type' ORDER BY fileactivitydate DESC ".$pagination, $this->returnFields);
            } catch (DBALException $e) {
            	$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;	
            }
            $returnArray = array();
            foreach($result as $row)
            {
                array_push($returnArray, $row);
            }
            return $returnArray;
        }
    }

    //---------------------------------------
    // Return file information based on filervoucheruid
    // 
    public function getFile($dataitem) {

        global $config;

        // check authentication as File UID is returned

        $vid = $dataitem['filevoucheruid'];
		try {
			$result = $this->db->query("SELECT * FROM files where filevoucheruid = '%s'", $vid);	
		} catch (DBALException $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;	
		}
       
        $returnArray = array();
        foreach($result as $row){
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //---------------------------------------
    // Return voucher information based on filervoucheruid
    // 
    public function getVoucher($vid) {

        global $config;

        // check authentication as file UID is returned
		try {
			$result = $this->db->query("SELECT * FROM files where fileid = '%s'", $vid);
		} catch (DBALException $e) {
			$this->saveLog->saveLog("","Error",$e->getMessage()); return FALSE;		
		}

        $returnArray = array();
        foreach($result as $row){
            array_push($returnArray, $row);
        }
        return $returnArray;
    }
	
	   //---------------------------------------
    // Return voucher information based on filervoucheruid
    // 
    public function getVoucherData($vid) {

        global $config;

        // check authentication as file UID is returned
		try {
	        $result = $this->db->query("SELECT * FROM files where filevoucheruid = '%s'", $vid);
		} catch (DBALException $e) {
			$this->saveLog->saveLog($dataitem,"Error",$e->getMessage()); return FALSE;		
		}

        $returnArray = array();
        foreach($result as $row){
            array_push($returnArray, $row);
        }
        return $returnArray;
    }

    //---------------------------------------
    // Email and log when a file is downloaded
    // 
    public function downloadedFile() {

        global $config;


        $jsonString = rawurldecode($_POST['jsonSendData']);
        $jsonString = utf8_encode($jsonString);
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


       	global $config;
        $dbCheck = DB_Input_Checks::getInstance();

        $jsonString = rawurldecode($_POST['jsonSendData']);
        $jsonString = utf8_encode($jsonString);
        $data = json_decode($jsonString, true);
        $dataitem = $data[0];

        // check if filevoucheruid exists or exit
        if($dataitem['filevoucheruid'] == "")
        {
            return "dataMissing";
        }
		
		// check if user supplied date is past the server configuration maximum date
		if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day"))
		{
		// reset fileexpiry date to max config date from server
		$dataitem["fileexpirydate"] = date($config['db_dateformat'],strtotime("+".($config['default_daysvalid'])." day"));
		}

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
            $dataitem['fileauthuseruid'] = $authAttributes["saml_uid_attribute"] ;
            $dataitem['fileauthuseremail'] = $authAttributes["email"];
        }

        try {
        	
	        $result = $this->db->exec("
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

	            date($config['db_dateformat'], strtotime($dataitem['fileexpirydate'])),
	            $dataitem['fileto'],
	            isset($dataitem['filesubject']) ? $dataitem['filesubject'] : "NULL",
	            date($config['db_dateformat'], time()),
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
	                $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']),
	                $dataitem['filesendersname'],
	                $dataitem['filereceiversname'],
	                $dataitem['filevouchertype'],
	                ensureSaneFileUid($dataitem['fileuid']),
	                $dataitem['fileauthuseruid'],
	                $dataitem["fileauthuseremail"],
	                date($config['db_dateformat'], time())
	            );
		} catch(DBALException $e) {
			 $this->saveLog->saveLog($dataitem,"Error",$e.getMessage()); return FALSE;
		}

        if($dataitem['filestatus'] == "Voucher") {
            $this->saveLog->saveLog($dataitem,"Voucher Sent","");
            return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
        } else {
            $this->saveLog->saveLog($dataitem,"Uploaded","");
            return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
        }
    }

// added for HTML5 version
public function insertVoucher($to,$expiry){


        global $config;
        $dbCheck = DB_Input_Checks::getInstance();
		$authAttributes = $this->authsaml->sAuth();
		// var  $dataitem = [];
		
		 $dataitem['fileexpirydate'] = $expiry;
         $dataitem['fileto'] = $to;
         $dataitem['filesubject'] = 'Voucher';
         $dataitem['fileactivitydate'] = '';
         $dataitem['filevoucheruid'] = getGUID();
         $dataitem['filemessage'] = '';
         $dataitem['filefrom'] = '';
         $dataitem['filesize'] = 0;
         $dataitem['fileoriginalname'] = '';
         $dataitem['filestatus'] = "Voucher";
         $dataitem['fileip4address'] = '';
         $dataitem['fileip6address'] = '';
         $dataitem['filesendersname'] = '';
         $dataitem['filereceiversname'] = '';
         $dataitem['filevouchertype'] = '';
         $dataitem['fileuid'] = getGUID();
         $dataitem['fileauthuseruid'] = $authAttributes["saml_uid_attribute"];
         $dataitem['fileauthuseremail'] = $authAttributes["email"];
         $dataitem['filecreateddate'] =  date($config['db_dateformat'], time());
		 
		 if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
            $dataitem['fileauthuseruid'] = $authAttributes["saml_uid_attribute"] ;
            $dataitem['fileauthuseremail'] = $authAttributes["email"];
			 $dataitem['filefrom'] = $authAttributes["email"];
		
        }
		
				
  		// check if user supplied date is past the server configuration maximum date
		if(strtotime($expiry) > strtotime("+".$config['default_daysvalid']." day"))
		{
		// reset fileexpiry date to max config date from server
		$expiry = date($config['db_dateformat'],strtotime("+".($config['default_daysvalid'])." day"));
		}

       $dataitem['fileexpirydate'] = $expiry;

        	
       $result = $this->db->exec("
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
  			date($config['db_dateformat'], strtotime($dataitem['fileexpirydate'])),
            $dataitem['fileto'],
            isset($dataitem['filesubject']) ? $dataitem['filesubject'] : "NULL",
            date($config['db_dateformat'], time()),
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
                $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']),
                $dataitem['filesendersname'],
                $dataitem['filereceiversname'],
                $dataitem['filevouchertype'],
                ensureSaneFileUid($dataitem['fileuid']),
                $dataitem['fileauthuseruid'],
                $dataitem["fileauthuseremail"],
                date($config['db_dateformat'], time())
            );

       		// if (!$result) { $this->saveLog->saveLog($dataitem,"Error",pg_last_error()); return FALSE; }

            if($dataitem['filestatus'] == "Voucher") {
                $this->saveLog->saveLog($dataitem,"Voucher Sent","");
                return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
            } else {
                $this->saveLog->saveLog($dataitem,"Uploaded","");
                return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
            }
    }


   //---------------------------------------
    // Insert new file or voucher HTML5
    // 
    public function insertFileHTML5($dataitem){


        global $config;
        $dbCheck = DB_Input_Checks::getInstance();

       
	    // check if filevoucheruid exists or exit
        if($dataitem['filevoucheruid'] == "")
        {
            return "dataMissing";
        }
		
		// check if user supplied date is past the server configuration maximum date
		if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day"))
		{
		// reset fileexpiry date to max config date from server
		$dataitem["fileexpirydate"] = date($config['db_dateformat'],strtotime("+".($config['default_daysvalid'])." day"));
		}

        try {
			$result = $this->db->exec("
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

            date($config['db_dateformat'], strtotime($dataitem['fileexpirydate'])),
            $dataitem['fileto'],
            isset($dataitem['filesubject']) ? $dataitem['filesubject'] : "NULL",
            date($config['db_dateformat'], time()),
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
                $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']),
				isset($dataitem['filesendersname']) ? $dataitem['filesendersname'] : "NULL",
				isset($dataitem['filereceiversname']) ? $dataitem['filereceiversname'] : "NULL",
				isset($dataitem['filevouchertype']) ? $dataitem['filevouchertype'] : "NULL",
                ensureSaneFileUid($dataitem['fileuid']),
                $dataitem['fileauthuseruid'],
                $dataitem["fileauthuseremail"],
                date($config['db_dateformat'], time())
            );
		} catch(DBALException $e) {
			$this->saveLog->saveLog($dataitem,"Error",$e->getMessage()); return FALSE;
		}

            if($dataitem['filestatus'] == "Voucher") {
                $this->saveLog->saveLog($dataitem,"Voucher Sent","");
                return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
            } else {
                $this->saveLog->saveLog($dataitem,"Uploaded","");
                return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
            }
			return true;
    }

    //---------------------------------------
    // Update file or voucher
    // 	
    public function updateFile(){

       	global $config;

        $dbCheck = DB_Input_Checks::getInstance();

        $jsonString = rawurldecode($_POST['jsonSendData']);
        $jsonString = utf8_encode($jsonString);
        $data = json_decode($jsonString, true);
        $dataitem = $data[0];

        $ip = $_SERVER['REMOTE_ADDR'];

        $fileexpirydate 	=  date($config['db_dateformat'],strtotime($dataitem['fileexpirydate']));
		
			// check if user supplied date is past the server configuration maximum date
		if(strtotime($fileexpirydate) > strtotime("+".$config['default_daysvalid']." day"))
		{
		$fileexpirydate = date($config['db_dateformat'],strtotime("+".$config['default_daysvalid']." day"));
		}
		
        $fileto			=  $dataitem['fileto'];

        if(isset($dataitem['filesubject'])){ 
            $filesubject 	= $dataitem['filesubject'];
        } else {
            $filesubject 	= "NULL";
        }
        $fileactivitydate 	= dateConversion($dbCheck->checkString($dataitem['fileactivitydate']));//date($config['db_dateformat'],time());//dateConversion($dbCheck->checkString($dataitem['fileactivitydate']));
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
        $fileip6address 	= $dbCheck->checkIp6($ip);
        $filesendersname 	= "NULL";//stringconversion(pg_escape_string($dataitem['filesendersname']));
        $filereceiversname 	= "NULL";//  stringconversion(pg_escape_string($dataitem['filereceiversname']));
        $filevouchertype 	= "NULL";// stringconversion(pg_escape_string($dataitem['filevouchertype']));
        $fileuid 		= ensureSaneFileUid($dataitem['fileuid']);
        $fileauthuseruid 	= $dataitem["fileauthuseruid"];
        $fileauthuseremail 	= $dataitem["fileauthuseremail"];
        $filecreateddate 	= date($config['db_dateformat'],strtotime($dataitem['filecreateddate']));

        try {
			if (isset($dataitem['fileid'])) {
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

	            $result = $this->db->exec($sqlQuery,
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
			}
		} catch(DBALException $e) {
			$this->saveLog->saveLog($dataitem,"Error",$e->getMessage()); return FALSE;
		}

         $this->saveLog->saveLog($dataitem,"Updated","Using Voucher");
         return true;//sendmail->sendEmail($dataitem,$config['defaultmailsendbody']);

        }	
    //}	
    //---------------------------------------
    // Delete a voucher
    // 
    public function deleteVoucher($fileid){

        global $config;

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

            $this->db->exec($sqlQuery, $fileid);

            $fileArray =  $this->getVoucher($fileid);

            $this->sendmail->sendEmail($fileArray[0],$config['defaultvouchercancelled']);	
            $this->saveLog->saveLog($fileArray[0],"Voucher Cancelled","");

            return true;
        } else {
            return false;
        }	
    }
    //---------------------------------------
    // Close a voucher
    // 
    public function closeVoucher($fileid){

        global $config;


        $dbCheck = DB_Input_Checks::getInstance();	

        $sqlQuery = "
            UPDATE 
            files 
            SET 
            filestatus = 'Closed' 
            WHERE 
            fileid = %d
            ";

        $this->db->exec($sqlQuery, $fileid);

        $fileArray =  $this->getVoucher($fileid);

        //$this->sendmail->sendEmail($fileArray[0],$config['defaultvouchercancelled']);	
        $this->saveLog->saveLog($fileArray[0],"Voucher Closed","");
        return true;

    }
    //---------------------------------------
    // Delete a file
    // 
    public function deleteFile($fileid){

       	global $config;

        // check authentication SAML User
        if( $this->authsaml->isAuth()) {

            $dbCheck = DB_Input_Checks::getInstance();	

            $sqlQuery = "
                UPDATE 
                files 
                SET 
                filestatus = 'Closed' 
                WHERE 
                fileid = %d
                ";

            $this->db->exec($sqlQuery, $fileid);

            $fileArray =  $this->getVoucher($fileid);

            $this->sendmail->sendEmail($fileArray[0],$config['defaultfilecancelled']);	
            $this->saveLog->saveLog($fileArray[0],"File Cancelled","");

            return true;
        } else {
            return false;
        }	
    }

    //---------------------------------------
    // Return filesize as integer from php
    // Function also handles windows servers
    // 
    public function getFileSize($filename){


        global $config;;

        if($filename == "" ) {
            return;
        } else {
            $file = $config["site_temp_filestore"].sanitizeFilename($filename);
			//We should turn this into a switch/case, exhaustive with a default case
            if (file_exists($file)) {
				if (PHP_OS == "Darwin") {
	                $size = trim(shell_exec("stat -f %z ". escapeshellarg($file)));
				}
                else if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
                {
                    $size = trim(shell_exec("stat -c%s ". escapeshellarg($file)));
                } 
				else { 
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

        global $config;

        $result["site_filestore_total"] = disk_total_space($config['site_filestore']);   			// use absolute locations result in bytes
        $result["site_temp_filestore_total"] = disk_total_space($config['site_temp_filestore']);   			// use absolute locations
        $result["site_filestore_free"] = disk_free_space($config['site_filestore']);   			// use absolute locations
        $result["site_temp_filestore_free"] = disk_free_space($config['site_temp_filestore']);   			// use absolute locations

        return $result;

    }
	
	  //---------------------------------------
    // Load Language File
    // Returns JSON array

	//DEAD CODE???
    //public function loadLanguage() {
    //
    //    global $config;
    //
    //    require_once("../language/".$config['site_defaultlanguage'].".php");   			// use absolute locations result in bytes
    //    
    //    return;
    //
    //}


    //---------------------------------------
    // Move the file
    // move file from tmp directory to live directory and rename with Unique ID
    public function moveFile(){

        global $config;

        $jsonString = rawurldecode($_POST['jsonSendData']);
        $jsonString = utf8_encode($jsonString);
        $fileobj = json_decode($jsonString, true);
        $dataitem = $fileobj[0];

        // generate unique filename
        // tempFilename is created from md5((uid or vid)+originalfilename+filesize)
        $tempFilename = ""; 

        // add voucher id if this is a voucher request
        if ( $this->authvoucher->aVoucher() ) {
            $tempFilename .= $dataitem['filevoucheruid'];
        } 
        // else add SAML saml_uid_attribute
        else if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
            $tempFilename .= $authAttributes["saml_uid_attribute"];	
        }


        // add the file name
        $tempFilename .=  sanitizeFilename($dataitem['fileoriginalname']);

        // add the file size to the filename
        $tempFilename .=  $dataitem['filesize'];

        // md5 $tempFilename
        $tempFilename = md5($tempFilename).'.tmp';

        if( $dataitem['fileoriginalname'] == "" || $dataitem['fileuid'] == "" ) {
            //customError(LOG_ERR, "File name or UID not found ".$dataitem['fileoriginalname'] ." - ".dataitem['fileuid'] , "","");
            return false;
        } else {
            $filename = $config["site_temp_filestore"].sanitizeFilename($tempFilename);
            $newlocation = $config["site_filestore"].ensureSaneFileUid($dataitem['fileuid']).".tmp";
            if (file_exists( $filename)) {

                //check if file size is correct first
                //	if(getFileSize($filename) != $dataitem['filesize'])
                //	{
                //		customError(LOG_ERR, "File did not upload correctly - size is incorrect".$filename." to ".$newlocation, "","");
                //		$this->saveLog->saveLog($dataitem,"Error","File did not upload correctly - size is incorrect");
                //		return false;
                //	}

                // move it
                if(rename($filename, $newlocation))
                {
                    $this->saveLog->saveLog($dataitem,"File Moved","");
                    return true;
                } else {
                    customError(LOG_ERR, "Unable to move file ".$filename." to ".$newlocation, "","");
                    $this->saveLog->saveLog($dataitem,"Error","Cannot Move file from Temp Folder");
                    return false;
                }
            } else {
                customError(LOG_ERR, "Unable to find temp file ".$filename,"","");
                $this->saveLog->saveLog($dataitem,"Error","Cannot find File in Temp Folder");
                return false;
            }
        }

    }

}

?>
