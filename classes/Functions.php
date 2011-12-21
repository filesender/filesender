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
        $filename = preg_replace("/^\./", "_", $filename); //return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
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
        $this->db = DB::getInstance();
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
	//public function setScratchMessage($message) {
//		$_SESSION['scratch'] = $message;
//	}
//
//	//Append to the scratch message
//	public function appendScratchMessage($message) {
//		logEntry("In appendScratchMessage");
//		session_start();
//		logEntry("session_start called");
//		if(! array_key_exists('scratch',$_SESSION)) {session_register("scratch");}
//		logEntry("Key didn't exist, registered");
//		$_SESSION['scratch'] = $_SESSION['scratch'] .'<br/>' .$message;
//		logEntry("Message added");
//	}	
//	
//	public function getScratchMessage() {
//		logEntry('in getScratchMessage');
//		if(array_key_exists('scratch',$_SESSION)) {
//			logEntry('returning scratch message'. $_SESSION['scratch']);
//			return $_SESSION['scratch'];
//		} else {return '';}
//	}
//	
//	//Validate to: email addresses
//	public function validate_to()
//	{	
//		global $config;
//		logEntry("In validate_to");
//		$emailto = str_replace(",",";",$_POST["fileto"]);
//		$emailArray = preg_split("/;/", $emailto);
//		logEntry("emailArray splitted");
//		//To many recipients - err out
//		if(count($emailArray) > $config['max_email_recipients'] ) {return false;}
//		logEntry("emailArray not to long (not to any recipients)");
//		
//		//Check if we have valid email adddresses
//		//We do have well thought out regular expressions, but I saw this one which seems to work and is maintained...
//		//Anyway, easy to change if we want the regex in.
//		foreach($emailArray as $email) {
//			logEntry("in loop");
//			if(filter_var($email,FILTER_VALIDATE_EMAIL)) {continue;}
//			else {
//				logEntry("no match for ".$email." returning false");
//	            return false; 
//	        }
//		}
//		logEntry("emails ok");
//		return true;
//	}
//	
//	//Validate date
//	public function validate_date()
//	{
//		global $config;
//		//date does a clever thing: if a value for a day or month is too high, it does a "right shift and prepends a 0
//		//E.g. day 33 -> 03 This protects us against nn-existing dates
//		$_POST['fileexpirydate'] = (date($config['datedisplayformat'],strtotime($_POST["fileexpirydate"])));
//		logEntry("Expiry date now is ". $_POST['fileexpirydate']);
//		return true;
//	}
//	
//	//Validate AUP
//	public function validate_aup()
//	{
//		return isset($_POST["aup"]);
//	}
//
//	
//	// validate extension for banned file names
//	public function validate_extension() {
//		global $config;
//		$outcome = true;
//		foreach( $_POST as $key => $value) {
//			logEntry("POST parameter " . $key . " = " .$value);
//		}
//		$lastelem = array_pop(explode(".",$_POST['n']));
//		logEntry('Last element = ' . $lastelem);
//		$banned = explode(",",$config['ban_extension']);
//		foreach($banned as $key => $naughty) {
//			if($lastelem == $naughty) {$outcome = false; break;}
//		}
//		logEntry('Outcome = ' . $outcome);
//		return $outcome;
//	}
//
//	//Validate if the file is not 0 bytes long
//	public function validate_zero_filesize()
//	{	
//		logEntry("Is numeric ". is_numeric($_POST['total']));
//		if (isset($_POST["total"])) {return (! $_POST["total"] == 0);}
//	}
//	
//	
//	public function validatePlainUpload() {
//		
//		global $config;
//		
//		lang("_INVALID_MISSING_EMAIL");
//		
//		logEntry("in validatePlainUpload");
//		$all_good = true;
//		logEntry("0All good in validation = ".$all_good);
//		
//		if(! $this->validate_to()) { 
//			logEntry("0-1 All wrong in email validation = ".$all_good);
//			
//			$all_good = false;
//			$this->appendScratchMessage(lang("_INVALID_MISSING_EMAIL"));
//			logEntry("0-2 All wrong in email validation = appended message");
//			
//		}
//		logEntry("1 All still good in validation? = ".$all_good);
//		if(! $this->validate_date()) { 
//			logEntry("1-A All wrong in validation date = ".$all_good);
//			$all_good = false;
//			$this->appendScratchMessage(lang("_INVALID_DATE_FORMAT"));
//			logEntry("1-b All wrong in validation date = ".$all_good);
//		}
//		logEntry("2 All still good in validation? = ".$all_good);
//		if(! $this->validate_aup()) { 
//			logEntry("2-A All wrong in validation AuP = ".$all_good);
//			$all_good = false;
//			logEntry("all_good = ".$all_good);
//			$this->appendScratchMessage(lang("_AGREETOC"));
//			logEntry("Scratch ".lang("_AGREETOC"). " added.");
//		}
//		logEntry("3 All still good in validation ? = ".$all_good);
//		if(! $this->validate_extension()) { 
//			logEntry("3-A All wrong in validation extensions = ".$all_good);
//			$all_good = false;
//			$this->appendScratchMessage(lang("_INVALID_FILE_EXT"));
//		}
//		logEntry("4 All still good in validation? = ".$all_good);
//		if(! $this->validate_zero_filesize()) { 
//			logEntry("4-A All wrong in validation zero filesize = ".$all_good);
//			$all_good = false;
//			$this->appendScratchMessage(lang("_INVALID_FILESIZE_ZERO"));
//		}
//		
//		logEntry("5 All still good in validation = ".$all_good);
//		print_r($all_good);
//		//If somethings is wrong, redirect with message in the scratch space
//		if(! $all_good) {
//			logEntry('Redirecting to page with scratch');
//			header("Location: " . $config['site_url'].'index.php');
//			exit;
//		}
//	}

    //--------------------------------------- CHECKED
    // Return Basic Database Statistics e.g. Up xx Gb (files xx) | Down xx Gb (files xx)
    public function getStats() {

        global $config;

        $statString = "| UP: ";

        $statement =   $this->db->fquery("SELECT * FROM logs WHERE logtype='Uploaded'");
		$statement->execute();
		$count = $statement->rowCount();

        $statString = $statString.$count." files ";

        $statement = $this->db->fquery("SELECT SUM(logfilesize) as total_uploaded FROM logs WHERE logtype='Uploaded'");
		$statement->execute();
		$totalResult = $statement->fetch(PDO::FETCH_NUM);
		$totalResult = $totalResult[0];
        $statString = $statString."(".round($totalResult/1024/1024/1024)."GB) |" ;
		$stmnt = NULL;
		
      	$statement = $this->db->fquery("SELECT * FROM logs WHERE logtype='Download'");
      	$statement->execute();
		$count = $statement->rowCount();
        $statString = $statString." DOWN: ".$count." files ";
		
       	$statement =  $this->db->fquery("SELECT SUM(logfilesize) FROM logs WHERE logtype='Download'");
      	$statement->execute();
		$totalResult = $statement->fetch(PDO::FETCH_NUM);
		$totalResult = $totalResult[0];
        $statString = $statString."(".round($totalResult/1024/1024/1024)."GB) |";
		$stmnt = NULL;
		
        return $statString;

    }

    //--------------------------------------- CHECKED
    // Get Voucher for a specified user based on saml_uid_attribute
    public function getVouchers() {

       global $config;

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "";
        }
		
		$result =  $this->db->fquery("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = %s) AND filestatus = 'Voucher' ORDER BY fileactivitydate DESC",$authAttributes["saml_uid_attribute"]);
        $returnArray = array();
        foreach($result as $row)
        {
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //--------------------------------------- CHECKED
    // Get Files for a specified user based on saml_uid_attribute
    public function getUserFiles() {

        global $config;

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "nonvalue";
        }
        $result =  $this->db->fquery("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = %s) AND filestatus = 'Available'  ORDER BY fileactivitydate DESC", $authAttributes["saml_uid_attribute"]);
           
        $returnArray = array();
        foreach($result as $row )
        {
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //--------------------------------------- CHECKED
    // Return logs if users is admin
    // current email authenticated as per config["admin"]
    public function adminLogs($type) {

        // check if this user has admin access before returning data
		global $page;
		global $total_pages;
		$pagination = "";
		$maxitems_perpage = 20;
		$page = 1;
		
		$statement = $this->db->fquery("SELECT count(logtype)  FROM logs WHERE logtype = '$type'");
		$statement->execute();
		$total = $statement->fetch(PDO::FETCH_NUM);
		$total = $total[0];

		$total_pages[$type] = ceil($total/$maxitems_perpage);
		
		if(isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"])) // protect SQLinjection by confirming  $_REQUEST["page"] is an integer only
		{
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

        $result =  $this->db->fquery("SELECT logtype, logfrom , logto, logdate, logfilesize, logfilename, logmessage FROM logs WHERE logtype = '$type' ORDER BY logdate DESC ".$pagination);
		$returnArray = array();
	        foreach($result as $row) 
            {
                array_push($returnArray, $row);
            }
            return $returnArray;
        }
    }

    //---------------------------------------CHECKED
    // Return Files if users is admin
    // current email authenticated as per config["admin"]
    public function adminFiles($type) {

		global $page;
		global $total_pages;
		$pagination = "";
		$maxitems_perpage = 10;
		$page = 1;
		
		$statement = $this->db->fquery("SELECT count(fileid) FROM files WHERE filestatus = '$type'");
		$statement->execute();
		$total = $statement->fetch(PDO::FETCH_NUM);
		$total = $total[0];
		
		$total_pages[$type] = ceil($total/$maxitems_perpage);
		
		if(isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"])) // protect SQLinjection by confirming  $_REQUEST["page"] is an integer only
		{
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
			$result =  $this->db->fquery("SELECT ".$this->returnFields." FROM files WHERE filestatus = '$type' ORDER BY fileactivitydate DESC ".$pagination);
			$returnArray = array();
			foreach($result as $row)
			{
				array_push($returnArray, $row);
			}
		return $returnArray;
		}
	}

    //--------------------------------------- CHECKED
    // Return file information based on filervoucheruid
    // 
    public function getFile($dataitem) {

        global $config;

        // check authentication as File UID is returned

        $vid = $dataitem['filevoucheruid'];
       	$result =  $this->db->fquery("SELECT * FROM files where filevoucheruid = %s", $vid);

        $returnArray = array();
        foreach($result as $row){
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //--------------------------------------- CHECKED NOTE
	// Note: Function Name Duplicated in AuthVoucher.php but using $_Request["vid"]
	// Note: Remove AuthVoucher.php getVocuher function and replace with similar function in Functions class
    // Return voucher information based on fileid
    // 
    public function getVoucher($vid) {

        global $config;

        // check authentication as file UID is returned
		$result =  $this->db->fquery("SELECT * FROM files where fileid = %s", $vid);
		$returnArray = array();
		foreach($result as $row){
			array_push($returnArray, $row);
		}
		return $returnArray;
		}
	
	//--------------------------------------- CHECKED
	// Return voucher information based on filervoucheruid
	// 
	public function getVoucherData($vid) {

		global $config;

		// check authentication as file UID is returned
  		$result =  $this->db->fquery("SELECT * FROM files where filevoucheruid = %s", $vid);
        $returnArray = array();
        foreach($result as $row){
            array_push($returnArray, $row);
        }
        return $returnArray[0];
    }
	
	//--------------------------------------- NOTE Replace with PDO version
	// added for HTML5 version
	public function insertVoucher($to,$expiry){


        global $config;
        $dbCheck = DB_Input_Checks::getInstance();
		$authAttributes = $this->authsaml->sAuth();
		// var  $dataitem = [];
		
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
		$dataitem['fileexpirydate'] =$expiry;
		        	
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
            ( %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
  			date($config['db_dateformat'], strtotime($expiry)),
            $dataitem['fileto'],
            $dataitem['filesubject'],
            date($config['db_dateformat'], time()),
            $dataitem['filevoucheruid'],
            $dataitem['filemessage'],
            $dataitem['filefrom'],
            $dataitem['filesize'],
            // inserted vouchers have no filenames, but inserted files must have a non-empty filename
           	$dataitem['fileoriginalname'],
            $dataitem['filestatus'],
            $dbCheck->checkIp($_SERVER['REMOTE_ADDR']),
            $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']),
            $dataitem['filesendersname'],
            $dataitem['filereceiversname'],
            $dataitem['filevouchertype'],
            $dataitem['fileuid'],
            $dataitem['fileauthuseruid'],
            $dataitem["fileauthuseremail"],
            date($config['db_dateformat'], time())
            );

            if($dataitem['filestatus'] == "Voucher") {
                $this->saveLog->saveLog($dataitem,"Voucher Sent","");
                return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
            } else {
                $this->saveLog->saveLog($dataitem,"Uploaded","");
                return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
            }
    }
	
	// ---------------------------- CHECKED
	// ensure valid fileexpirydate
	public function ensureValidFileExpiryDate($data)
	{
		global $config;
		// check fileexpirydate exists and is valid
		if((strtotime($data) >= strtotime("+".$config['default_daysvalid']." day") ||  strtotime($data) <= strtotime("now")))
		{
			// reset fileexpiry date to max config date from server
			$data = date($config['db_dateformat'],strtotime("+".($config['default_daysvalid'])." day"));
		} 
		return date($config['db_dateformat'],strtotime($data));
	}
	
	//
	//	function ensureValidFileTo($data)
	//	{
	//		global $config;
	//		return $data;
	//	}
	
   	//--------------------------------------- CHECKED
    // Validate $data and return data
    // 
	public function validateFileData($data)
	{
		// client must provide following minimum data
		// fileto // filesize // filefrom // fileexpirydata // file voucher or authenticated uuid // filename
		// ensure they exists and are valid
		// return array of errors or 
		global $config;
		global $resultArray;
		
		$dbCheck = DB_Input_Checks::getInstance();
	
		$errorArray = array();
		// test 
		//array_push($errorArray, "err_nodiskspace");
		//array_push($errorArray, "err_tomissing");
		// filesize missing
		if(!isset($data["filesize"])){ array_push($errorArray, "err_missingfilesize"); }
		// check space is available on disk before uploading
		if(isset($data["filesize"]) && disk_free_space($config['site_filestore']) - $data["filesize"] < 1) { array_push($errorArray, "err_nodiskspace");} 
		// expiry missing
		if(!isset($data["fileexpirydate"])){ array_push($errorArray,  "err_expmissing"); }
		// fileto missing
		if(!isset($data["fileto"])){ array_push($resultArray, "err_tomissing");}
		// filename missing
		if(!isset($data["fileoriginalname"])){ array_push($errorArray, "err_invalidfilename");}
		// expiry out of range
		if(strtotime($data["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($data["fileexpirydate"]) < strtotime("now"))
		{ array_push($resultArray,"err_exoutofrange");}
		// emmail missing
		if(!isset($data["fileto"])){ array_push($errorArray,  "err_filetomissing"); 
		} else {
		$emailto = str_replace(",",";",$data["fileto"]);
		$emailArray = preg_split("/;/", $emailto);
		// validate number of emails
		if(count($emailArray) > $config['max_email_recipients'] ) {array_push($errorArray,  "err_toomanyemail");}
		// validate individual emails
		foreach ($emailArray as $Email) {
			if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) {array_push($errorArray, "err_invalidemail");}
		}
		}
		// if errors - return them via json to client
		
		if(count($errorArray) > 0 )
		{
		$resultArray["errors"] =  $errorArray;
		echo json_encode($resultArray);
		exit; // Stop further script execution
		}
			
		// no errors >> continue
		// ensure valid fields before commiting to database
		$data["fileexpirydate"] = $this->ensureValidFileExpiryDate($data["fileexpirydate"]);
		$data["filesubject"] = (isset($data["filesubject"])) ? $data["filesubject"] : "";
		$data["fileactivitydate"]= date($config['db_dateformat'], time());
		$data["filevoucheruid"] = (isset($data["filevoucheruid"])) ? $data["filevoucheruid"] : getGUID();
		$data["filemessage"] = (isset($data["filemessage"])) ? $data["filemessage"] : "";
        $data["filefrom"]=$data["filefrom"];
        $data["filesize"]=$data["filesize"];
        $data["fileoriginalname"]=  sanitizeFilename($data['fileoriginalname']);
        $data["filestatus"]="pending";//isset($data['filestatus']) ? $data['filestatus'] : "pending";
        $data["fileip4address"]= $dbCheck->checkIp($_SERVER['REMOTE_ADDR']);
        $data["fileip6address"]= $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']);
		$data["filesendersname"]=isset($data['filesendersname']) ? $data['filesendersname'] : NULL;
		$data["filereceiversname"]=isset($data['filereceiversname']) ? $data['filereceiversname'] : NULL;
		$data["filevouchertype"]=isset($data['filevouchertype']) ? $data['filevouchertype'] : NULL;
        $data["fileuid"]=getGUID();
        //$data["fileauthuseruid"]="null";
        //$data["fileauthuseremail"]="null";
        $data["filecreateddate"]= date($config['db_dateformat'], time()); 
		
		return $data;
	}
	
	//--------------------------------------- CHECKED
	// Insert new file or voucher HTML5
	// 
	public function insertFileHTML5($dataitem){

        global $config;

		// prepare PDO insert statement
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('INSERT INTO files (
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
            ( 	:fileexpirydate,
               	:fileto,
             	:filesubject,
               	:fileactivitydate,
               	:filevoucheruid,
               	:filemessage,
               	:filefrom,
               	:filesize,
               	:fileoriginalname,
               	:filestatus,
               	:fileip4address,
               	:fileip6address,
               	:filesendersname,
               	:filereceiversname,
               	:filevouchertype,
               	:fileuid,
               	:fileauthuseruid,
               	:fileauthuseremail,
               	:filecreateddate)');	
				
	$statement->bindParam(':fileexpirydate', $dataitem['fileexpirydate']);
	$statement->bindParam(':fileto', $dataitem['fileto']);
	$statement->bindParam(':filesubject', $dataitem['filesubject']);
	$statement->bindParam(':fileactivitydate', $dataitem['fileactivitydate']);
	$statement->bindParam(':filevoucheruid', $dataitem['filevoucheruid']);
	$statement->bindParam(':filemessage', $dataitem['filemessage']);
	$statement->bindParam(':filefrom', $dataitem['filefrom']);
	$statement->bindParam(':filesize', $dataitem['filesize']);
	$statement->bindParam(':fileoriginalname', $dataitem['fileoriginalname']);
	$statement->bindParam(':filestatus', $dataitem['filestatus']);
	$statement->bindParam(':fileip4address', $dataitem['fileip4address']);
	$statement->bindParam(':fileip6address', $dataitem['fileip6address']);
	$statement->bindParam(':filesendersname', $dataitem['filesendersname']);
	$statement->bindParam(':filereceiversname', $dataitem['filereceiversname']);
	$statement->bindParam(':filevouchertype', $dataitem['filevouchertype']);
	$statement->bindParam(':fileuid', $dataitem['fileuid']);
	$statement->bindParam(':fileauthuseruid', $dataitem['fileauthuseruid']);
	$statement->bindParam(':fileauthuseremail', $dataitem['fileauthuseremail']);
	$statement->bindParam(':filecreateddate', $dataitem['filecreateddate']);

	$result = $statement->execute();    

		if($dataitem['filestatus'] == "Voucher") {
			$this->saveLog->saveLog($dataitem,"Voucher Sent","");
				return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
			} elseif ($dataitem['filestatus'] == "Available") {
				$this->saveLog->saveLog($dataitem,"Uploaded","");
				return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
			}
			return true;
    }
	
    //--------------------------------------- CHECKED
    // Delete a voucher
    // 
    public function deleteVoucher($fileid){

        global $config;

        // check authentication SAML User
        if( $this->authsaml->isAuth()) {

            $sqlQuery = "
                UPDATE 
                files 
                SET 
                filestatus = 'Voucher Cancelled' 
                WHERE 
                fileid = %s
                ";

           $statement = $this->db->fquery($sqlQuery, $fileid);
		   
		   $fileArray =  $this->getVoucher($fileid);
           $this->sendmail->sendEmail($fileArray[0],$config['defaultvouchercancelled']);	
           $this->saveLog->saveLog($fileArray[0],"Voucher Cancelled","");

            return true;
        } else {
            return false;
        }	
    }
    //--------------------------------------- CHECKED
    // Close a voucher
    // 
    public function closeVoucher($fileid){

        $dbCheck = DB_Input_Checks::getInstance();	

        $sqlQuery = "
            UPDATE 
            files 
            SET 
            filestatus = 'Closed' 
            WHERE 
            fileid = %s
            ";

        $statement = $this->db->fquery($sqlQuery, $fileid);
        $fileArray =  $this->getVoucher($fileid);
        $this->saveLog->saveLog($fileArray[0],"Voucher Closed","");
        return true;
    }
	
    //--------------------------------------- CHECKED
    // Delete a file
    // 
    public function deleteFile($fileid){

       	global $config;

        // check authentication SAML User
        if( $this->authsaml->isAuth()) {

    	        $sqlQuery = "
                UPDATE 
                files 
                SET 
                filestatus = 'Closed' 
                WHERE 
                fileid = %s
                ";

            $this->db->fquery($sqlQuery, $fileid);
            $fileArray =  $this->getVoucher($fileid);
            $this->sendmail->sendEmail($fileArray[0],$config['defaultfilecancelled']);	
            $this->saveLog->saveLog($fileArray[0],"File Cancelled","");

            return true;
        } else {
            return false;
        }	
    }

    //--------------------------------------- CHECKED
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

    //--------------------------------------- CHECKED
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
}
?>
