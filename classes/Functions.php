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

// ---------------------------------------
// Format bytes into readbable text format
// ---------------------------------------
function formatBytes($bytes, $precision = 2) {

    if($bytes >  0) 
    {
        $units = array(' Bytes', ' kB', ' MB', ' GB', ' TB');

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
// ---------------------------------------
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

// ---------------------------------------
// Replace illegal chars with _ character in supplied filenames
// ---------------------------------------

function sanitizeFilename($filename){

    if (!empty($filename)) {
        $filename = preg_replace("/^\./", "_", $filename); //return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
        return $filename;
    } else {
        //trigger_error("invalid empty filename", E_USER_ERROR);
        return "";
    }
}

// ---------------------------------------
// Error if fileUid doesn't look sane
// ---------------------------------------

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
   public $returnFields = " fileid, fileexpirydate, fileto , filesubject, fileactivitydate, filemessage, filefrom, filesize, fileoriginalname, filestatus, fileip4address, fileip6address, filesendersname, filereceiversname, filevouchertype, fileauthuseruid, fileauthuseremail, filecreateddate, fileauthurl, fileoptions, fileuid, filevoucheruid ";	

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
	
    //--------------------------------------- CHECKED
    // Return Basic Database Statistics e.g. Up xx Gb (files xx) | Down xx Gb (files xx)
	// ---------------------------------------
    public function getStats() {

        global $config;

        $statString = "| UP: ";

        $statement =   $this->db->fquery("SELECT COUNT(*) FROM logs WHERE logtype='Uploaded'");
		$statement->execute();
		$count = $statement->fetchColumn();

        $statString = $statString.$count." files ";

        $statement = $this->db->fquery("SELECT SUM(logfilesize) as total_uploaded FROM logs WHERE logtype='Uploaded'");
		$statement->execute();
		$totalResult = $statement->fetch(PDO::FETCH_NUM);
		$totalResult = $totalResult[0];
        $statString = $statString."(".round($totalResult/1024/1024/1024)."GB) |" ;
		$stmnt = NULL;
		
      	$statement = $this->db->fquery("SELECT COUNT(*) FROM logs WHERE logtype='Download'");
      	$statement->execute();
		$count = $statement->fetchColumn();
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
	// ---------------------------------------
    public function getVouchers() {

       global $config;

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "";
        }
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = :fileauthuseruid) AND filestatus = 'Voucher'  ORDER BY fileactivitydate DESC");
		$statement->bindParam(':fileauthuseruid', $authAttributes["saml_uid_attribute"]);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage());
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
		$returnArray = array();
        foreach($result as $row)
        {
            array_push($returnArray, $row);
        }
        return json_encode($returnArray);
    }

    //--------------------------------------- CHECKED
    // Get Files for a specified user based on saml_uid_attribute
	// ---------------------------------------
    public function getUserFiles() {

        global $config;

        if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "nonvalue";
        }
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT ".$this->returnFields." FROM files WHERE (fileauthuseruid = :fileauthuseruid) AND filestatus = 'Available'  ORDER BY filecreateddate DESC");
		$statement->bindParam(':fileauthuseruid', $authAttributes["saml_uid_attribute"]);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
		$returnArray = array();
		foreach($result as $row )
		{
			// return number of downloads for a file
			$row["downloads"] =  $this->countDownloads($row["filevoucheruid"]);
			$row["downloadsummary"] = $this->downloadSummary($row["filevoucheruid"]);
			array_push($returnArray, $row);
		}
		return json_encode($returnArray);
    }
	
 	//--------------------------------------- CHECKED
    // returns download summary as array for a specified voucher
	// ---------------------------------------
	public function downloadSummary($vid)
	{
		global $config;
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT * FROM logs WHERE  logvoucheruid = :logvoucheruid AND logtype = 'Download' ORDER BY logdate DESC");
		$statement->bindParam(':logvoucheruid', $vid);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
		$returnArray = array();
		foreach($result as $row )
		{
			array_push($returnArray, $row);
		}
		return $returnArray ; 
	}
	
	//----------------------------------------
	// returns unique emails for autocomplete for current user
	// ---------------------------------------
	public function uniqueemailsforautocomplete()
	{
		global $config;
		 if( $this->authsaml->isAuth()) {
            $authAttributes = $this->authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "nonvalue";
        }
		// limit results by config option
		$count = isset($config["autocompleteHistoryMax"])? "LIMIT ".$config["autocompleteHistoryMax"]:"";
		$sortby = "fileto"; 
		$order = (isset($config["autocompleteSortOrder"]) && $config["autocompleteSortOrder"] == "DESC")? "DESC":"ASC";
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT DISTINCT fileto FROM files WHERE  fileauthuseruid = :fileauthuseruid  ORDER BY ".$sortby." ".$order." ".$count);
		$statement->bindParam(':fileauthuseruid', $authAttributes["saml_uid_attribute"]);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$returnArray = array();
	    foreach($result as $row) 
        {
                array_push($returnArray, "'".addslashes($row["fileto"])."'");
       }
		$commaList = implode(', ', $returnArray);
		$pdo = NULL;
		return $commaList ; 
	}
    
	 //--------------------------------------- CHECKED
    // returns the number of downloads for a file
	// ---------------------------------------
	public function countDownloads($vid)
	{
		global $config;
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT count(*)  FROM logs WHERE logvoucheruid = :logvoucheruid AND logtype = 'Download'");
		
		$statement->bindParam(':logvoucheruid', $vid);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$total = $statement->fetch(PDO::FETCH_NUM);
		return $total[0];
	}
	
    //--------------------------------------- CHECKED
    // Return logs if users is admin
    // current email authenticated as per config["admin"]
	// ---------------------------------------
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
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('SELECT logtype, logfrom , logto, logdate, logfilesize, logfilename, logmessage FROM logs WHERE logtype = :logtype ORDER BY logdate DESC '.$pagination);
		$statement->bindParam(':logtype', $type);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
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
	// ---------------------------------------
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
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('SELECT '.$this->returnFields.' FROM files WHERE filestatus = :filestatus ORDER BY fileactivitydate DESC '. $pagination);
		$statement->bindParam(':filestatus', $type);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage());
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
		$returnArray = array();
		foreach($result as $row)
		{
			array_push($returnArray, $row);
		}
		return $returnArray;
		}
	}

	// check if this upload already has a data entry
	public function checkPending($dataitem) {
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("SELECT * FROM files where fileoriginalname = :fileoriginalname AND filesize = :filesize AND fileuid = :fileuid AND filestatus = 'Pending'");
		$statement->bindParam(':fileoriginalname', $dataitem["fileoriginalname"]);
		$statement->bindParam(':filesize', $dataitem["filesize"]);
		$statement->bindParam(':fileuid', $dataitem["fileuid"]);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage());
		}   
		$result = $statement->fetchAll();
		if($result)
		{
			return $result[0];
		} else {
			return "";
		}
		$pdo = NULL;
		
	}	
	
    //--------------------------------------- CHECKED
    // Return file information based on filervoucheruid
	// ---------------------------------------
    // 
    public function getFile($dataitem) {

		$vid = $dataitem['filevoucheruid'];
 
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('SELECT * FROM files where filevoucheruid = :filevoucheruid');
		$statement->bindParam(':filevoucheruid', $vid);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
		$returnArray = array();
		foreach($result as $row)
		{
			array_push($returnArray, $row);
		}
		return json_encode($returnArray);
	}

    //--------------------------------------- CHECKED NOTE
	// Note: Function Name Duplicated in AuthVoucher.php but using $_Request["vid"]
	// Note: Remove AuthVoucher.php getVocuher function and replace with similar function in Functions class
    // Return voucher information based on fileid
	// ---------------------------------------
    // 
    public function getVoucher($vid) {

       	$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('SELECT * FROM files where fileid = :fileid');
		$statement->bindParam(':fileid', $vid);
		try 
		{ 	
			$statement->execute(); 
		}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage());
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
  		$returnArray = array();
        foreach($result as $row)
		{
            array_push($returnArray, $row);
        }
		return $returnArray;
		}
	
	//--------------------------------------- CHECKED
	// Return voucher information based on filervoucheruid
	// ---------------------------------------
	// 
	public function getVoucherData($vid) {

		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('SELECT * FROM files where filevoucheruid = :filevoucheruid');
		$statement->bindParam(':filevoucheruid', $vid);
		try { 	
			$statement->execute(); 
			}
		catch(PDOException $e)
		{ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
		}   
		$result = $statement->fetchAll();
		$pdo = NULL;
  		$returnArray = array();
        foreach($result as $row)
		{
            array_push($returnArray, $row);
        }
        return $returnArray[0];
    }
	
	//--------------------------------------- CHECKED
	// insert a voucher
	// ---------------------------------------

	public function insertVoucher($to,$from,$expiry,$vouchermessage,$options){
		// must be authenticated
		if( $this->authsaml->isAuth()) {
			
        global $config;
		global $lang;
        $dbCheck = DB_Input_Checks::getInstance();
		$authAttributes = $this->authsaml->sAuth();
		
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
			filecreateddate,
			fileoptions 

            ) VALUES
            ( 
			:fileexpirydate,
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
			:filecreateddate,
			:fileoptions)');
			
			$filevoucheruid = getGUID();
			$voucher = 'Voucher';
			$voucherissuedemailsubject = (isset($config['voucherissuedemailsubject'])) ?  $config['voucherissuedemailsubject'] : "Voucher";
			// TODO: move all mail text to language files
			$voucherissuedemailsubject = (isset($lang['_VOUCHER_ISSUED_EMAIL_SUBJECT'])) ?  lang('_VOUCHER_ISSUED_EMAIL_SUBJECT') : $voucherissuedemailsubject;
			$blank = '';
			$zero = 0;
			$fileexpiryParam = date($config['db_dateformat'], strtotime($expiry));
			$statement->bindParam(':fileexpirydate',$fileexpiryParam);
			$statement->bindParam(':fileto', $to);
			$statement->bindParam(':filesubject', $voucherissuedemailsubject);
			$fileactivitydateParam =  date($config['db_dateformat'], time());
			$statement->bindParam(':fileactivitydate',$fileactivitydateParam );	
			$statement->bindParam(':filevoucheruid', $filevoucheruid );
			$statement->bindParam(':filemessage', $vouchermessage);
			$statement->bindParam(':filefrom', $from);
			$statement->bindParam(':filesize', $zero);
			$statement->bindParam(':fileoriginalname', $blank);
			$statement->bindParam(':filestatus', $voucher);
			$fileip4addressParam = $dbCheck->checkIp($_SERVER['REMOTE_ADDR']);
			$statement->bindParam(':fileip4address',$fileip4addressParam );
			$fileip6addressParam = $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']);
			$statement->bindParam(':fileip6address', $fileip6addressParam);
			$statement->bindParam(':filesendersname', $blank);
			$statement->bindParam(':filereceiversname', $blank);
			$statement->bindParam(':filevouchertype', $blank);
			$fileuidParam = getGUID();
			$statement->bindParam(':fileuid', $fileuidParam);
			$statement->bindParam(':fileauthuseruid', $authAttributes["saml_uid_attribute"]);
			$statement->bindParam(':fileauthuseremail', $from);
			$filecreateddateParam =  date($config['db_dateformat'], time());
			$statement->bindParam(':filecreateddate',$filecreateddateParam);
			$statement->bindParam(':fileoptions',$options);
			try { 	
			$statement->execute(); 
			}
			catch(PDOException $e){ 
			logEntry($e->getMessage(),"E_ERROR");	
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
			}   
			$pdo = NULL;
			// get voucherdata to email
			$dataitem = $this->getVoucherData($filevoucheruid);
			$this->saveLog->saveLog($dataitem,"Voucher Sent","");
			return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
			
		} else {
			
			return false;
		}
    }
	
	// --------------------------------------- CHECKED
	// ensure valid fileexpirydate
	// --------------------------------------- 
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
		
	// ---------------------------------------CHECKED
    // Validate $data and return data
	// ---------------------------------------
	public function validateFileData($data)
	{
		// client must provide following minimum data
		// fileto // filesize // filefrom // fileexpirydata // file voucher or authenticated uuid // filename
		// ensure they exists and are valid
		// return array of errors or 
		global $config;
		global $resultArray;
		
		$dbCheck = DB_Input_Checks::getInstance();
		$authsaml = AuthSaml::getInstance();
		$functions = Functions::getInstance();
		
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
		if(!isset($data["fileto"])){ array_push($errorArray, "err_tomissing");}
		// filename missing
		if(!isset($data["fileoriginalname"])){ array_push($errorArray, "err_invalidfilename");}
		// filename has invalid extension - $config['ban_extension'] as array
		$ban_extension = explode(',', $config['ban_extension']);
		foreach ($ban_extension as $extension) {
			if(isset($data["fileoriginalname"]) && $extension == pathinfo($data["fileoriginalname"], PATHINFO_EXTENSION) ){ array_push($errorArray, "err_invalidextension");}
		}
		// filename blank
		if(isset($data["fileoriginalname"]) && $data["fileoriginalname"] === ""){ array_push($errorArray, "err_invalidfilename");}
		// filename contains invalid characters
		if(isset($data["fileoriginalname"]) && preg_match('=^[^\\\\/:;\*\?\"<>|]+(\.[^\\\\/:;\*\?\"<>|]+)*$=',$data["fileoriginalname"]) === 0){ array_push($errorArray, "err_invalidfilename");}
		
		// expiry out of range
		if(strtotime($data["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($data["fileexpirydate"]) < strtotime("now"))
		{
			// Don't generate error, expiry date will be fixed later on with:
			// $data["fileexpirydate"] = $this->ensureValidFileExpiryDate($data["fileexpirydate"]);
			// When generating an error use/uncomment the following code:
			// array_push($errorArray,"err_exoutofrange");
		}
		// Recipient email missing
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
		// Sender email missing or not authuser or voucher sender
		if (!isset($data["filefrom"])){
			array_push($errorArray,  "err_filefrommissing");
		} else {
			// Check if sender address is valid
			if(!filter_var($data["filefrom"],FILTER_VALIDATE_EMAIL)) {array_push($errorArray, "err_invalidemail");}
			// check if filefrom matches voucher from or matches authenticated user
		if(isset($_SESSION['voucher']))
		{
			$tempData = $functions->getVoucherData($_SESSION['voucher']);
			//array_push($errorArray,  $data["filefrom"] .":". $tempData["filefrom"]);
			if($data["filefrom"] != $tempData["fileto"] ) {array_push($errorArray, "err_invalidemail");}
		}	else if( $authsaml->isAuth()) 
		{
			$authAttributes = $authsaml->sAuth();
			if ( !in_array($data["filefrom"],$authAttributes["email"]) ) {
				array_push($errorArray, "err_invalidemail");
			}
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
        $data["filestatus"]="Pending";//isset($data['filestatus']) ? $data['filestatus'] : "Pending";
        $data["fileip4address"]= $dbCheck->checkIp($_SERVER['REMOTE_ADDR']);
        $data["fileip6address"]= $dbCheck->checkIp6($_SERVER['REMOTE_ADDR']);
		$data["filesendersname"]=isset($data['filesendersname']) ? $data['filesendersname'] : NULL;
		$data["filereceiversname"]=isset($data['filereceiversname']) ? $data['filereceiversname'] : NULL;
		$data["filevouchertype"]=isset($data['filevouchertype']) ? $data['filevouchertype'] : NULL;
        if($data["fileuid"] == "" ) {$data["fileuid"] = getGUID();};
        //$data["fileauthuseruid"]="null";
        //$data["fileauthuseremail"]="null";
        $data["filecreateddate"]= date($config['db_dateformat'], time()); 
		
		return $data;
	}
	
	// --------------------------------------- CHECKED
	// Insert new file  
	// ---------------------------------------
	public function insertFile($dataitem){

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
			filecreateddate,
			fileoptions
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
			:filecreateddate,
			:fileoptions)');	
				
			$statement->bindParam(':fileexpirydate', $dataitem['fileexpirydate']);
			$statement->bindParam(':fileto', $dataitem['fileto']);
			$statement->bindParam(':filefrom', $dataitem['filefrom']);
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
			$statement->bindParam(':fileoptions', $dataitem['fileoptions']);
			
			try { 
				$statement->execute(); 
				}
			catch(PDOException $e){ 
				logEntry($e->getMessage(),"E_ERROR");	
				displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage());
				return false;
				}   

			if($dataitem['filestatus'] == "Voucher") {
				$this->saveLog->saveLog($dataitem,"Voucher Sent","");
				return $this->sendmail->sendEmail($dataitem,$config['voucherissuedemailbody']);
			} elseif ($dataitem['filestatus'] == "Available") {
				$this->saveLog->saveLog($dataitem,"Uploaded","");
				return $this->sendmail->sendEmail($dataitem,$config['fileuploadedemailbody']);
			}
			return true;
		}
	
	// --------------------------------------- CHECKED
	// Update file 
	// ---------------------------------------
	public function updateFile($dataitem){

        global $config;

		// prepare PDO insert statement
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare('UPDATE files SET
			fileexpirydate = :fileexpirydate,
			fileto = :fileto,
			filesubject = :filesubject,
			fileactivitydate = :fileactivitydate,
			filemessage = :filemessage,
			filefrom = :filefrom,
			filesize = :filesize,
			fileoriginalname = :fileoriginalname,
			filestatus = :filestatus,
			fileip4address = :fileip4address,
			fileip6address = :fileip6address,
			filesendersname = :filesendersname,
			filereceiversname = :filereceiversname,
			filevouchertype = :filevouchertype,
			fileuid = :fileuid,
			fileauthuseruid = :fileauthuseruid,
			fileauthuseremail = :fileauthuseremail,
			filecreateddate = :filecreateddate
			WHERE filevoucheruid = :filevoucheruid');	
				
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
	
			try { 
				$statement->execute(); 
				}
			catch(PDOException $e){ 
				logEntry($e->getMessage(),"E_ERROR");	
				displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
				return false;
				}   
			return true;
		}
    // --------------------------------------- CHECKED
    // Delete a voucher
    // ---------------------------------------
    public function deleteVoucher($fileid){

        global $config;

		if( $this->authsaml->isAuth()) { // check authentication SAML User
			
			$pdo = $this->db->connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
			$statement = $pdo->prepare("UPDATE files SET filestatus = 'Voucher Cancelled' WHERE fileid = :fileid");
			$statement->bindParam(':fileid', $fileid);
			
			try { $statement->execute();}
			catch(PDOException $e){ logEntry($e->getMessage(),"E_ERROR");	return false; }   
				
			$fileArray =  $this->getVoucher($fileid);
	
			if(count($fileArray) > 0) 
			{
				$this->sendmail->sendEmail($fileArray[0],$config['defaultvouchercancelled']);	
				$this->saveLog->saveLog($fileArray[0],"Voucher Cancelled","");
				return true;
			}
			return false;
		} else {
			return false;
		}	
	}
	
    // --------------------------------------- CHECKED
    // Close a voucher
    // ---------------------------------------
    public function closeVoucher($fileid){

        global $config;

		if( $this->authsaml->isAuth() || $this->authvoucher->aVoucher()) { // check authentication SAML User
			
			$pdo = $this->db->connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
			$statement = $pdo->prepare("UPDATE files SET filestatus = 'Closed' WHERE fileid = :fileid");
			$statement->bindParam(':fileid', $fileid);
			
			try { $statement->execute();}
			catch(PDOException $e){ logEntry($e->getMessage(),"E_ERROR");	return false; }   
				
			$fileArray =  $this->getVoucher($fileid);
	
			if(count($fileArray) > 0) 
			{
				$this->saveLog->saveLog($fileArray[0],"Voucher Cancelled","");
				return true;
			}
			return false;
		} else {
			return false;
		}	
    }
	
	 // --------------------------------------- CHECKED
    // Close a voucher
    // ---------------------------------------
    public function closeCompleteVoucher($filevoucheruid){

        global $config;

		if( $this->authsaml->isAuth() || $this->authvoucher->aVoucher()) { // check authentication SAML User
			
			$pdo = $this->db->connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
			$statement = $pdo->prepare("UPDATE files SET filestatus = 'Closed' WHERE filevoucheruid = :filevoucheruid");
			$statement->bindParam(':filevoucheruid', $filevoucheruid);
			
			try { $statement->execute();}
			catch(PDOException $e){ logEntry($e->getMessage(),"E_ERROR");	return false; }   
			
			logEntry("Voucher Closed: ".$filevoucheruid);	
			
			return true;
		
		} else {
			return false;
		}	
    }
	
    // --------------------------------------- CHECKED
    // Delete a file
    // ---------------------------------------
    public function deleteFile($fileid){

            global $config;

		if( $this->authsaml->isAuth()) { // check authentication SAML User
			
			$pdo = $this->db->connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
			$statement = $pdo->prepare("UPDATE files SET filestatus = 'Deleted' WHERE fileid = :fileid");
			$statement->bindParam(':fileid', $fileid);
			
			try { $statement->execute();}
			catch(PDOException $e){ logEntry($e->getMessage(),"E_ERROR");	return false; }   
				
			$fileArray =  $this->getVoucher($fileid);
	
			if(count($fileArray) > 0) 
			{
				$this->sendmail->sendEmail($fileArray[0],$config['defaultfilecancelled']);	
				$this->saveLog->saveLog($fileArray[0],"File Cancelled","");
				return true;
			}
			return false;
		} else {
			return false;
		}	
    }

    //--------------------------------------- CHECKED
    // Return filesize as integer from php
    // Function also handles windows servers
    // ---------------------------------------
    // 
    public function getFileSize($filename){

        global $config;

        if($filename == "" ) {
            return;
        } else {
            $file = $filename;//$config["site_filestore"].sanitizeFilename($filename);
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
	// ---------------------------------------
    public function driveSpace() {

        global $config;

        $result["site_filestore_total"] = disk_total_space($config['site_filestore']);   			// use absolute locations result in bytes
        $result["site_temp_filestore_total"] = disk_total_space($config['site_temp_filestore']);   			// use absolute locations
        $result["site_filestore_free"] = disk_free_space($config['site_filestore']);   			// use absolute locations
        $result["site_temp_filestore_free"] = disk_free_space($config['site_temp_filestore']);   			// use absolute locations

        return $result;

    }
	
	 //--------------------------------------- 
    // Check if table exists in DB
    // Returns 1 if found
	// ---------------------------------------
	public function tableExists($table)
	{
	// table exists requires a different script for postgres and mysql
	global $config;
	if ($config["db_type"] == 'pgsql') {
      $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE :table";
    }
    else {
      $query = "show tables like :table";
    }
	$pdo = $this->db->connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
	$statement = $pdo->prepare($query);
	$statement->bindParam(':table', $table);
	$statement->execute();
	$count = $statement->rowCount();
	//echo $count;
	return $count;
	}
	
	 //--------------------------------------- 
    // Check if table column exists in DB
    // Returns 1 if found
	// ---------------------------------------
	public function columnExists($table,$column)
	{
	// table column requires a different script for postgres and mysql
	global $config;
	if ($config["db_type"] == 'pgsql') 
	{
      $query = "SELECT column_name FROM information_schema.columns WHERE table_name=:table and column_name=:column";
    }
    else {
      $query = "SHOW COLUMNS FROM :table WHERE Field = :column";
    }
	$pdo = $this->db->connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
	$statement = $pdo->prepare($query);
	$statement->bindParam(':table', $table);
	$statement->bindParam(':column', $column);
	$statement->execute();
	$count = $statement->rowCount();
	//echo $count;
	return $count;
	}
	
	//--------------------------------------- 
    // DB version in database
    // Returns version number
	// ---------------------------------------
	public function dbVersion()
	{
	global $config;
	$pdo = $this->db->connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
	$statement = $pdo->prepare("SELECT configvalue FROM config WHERE configfield = 'DBVersion'");
	$statement->execute();
	$result = $statement->fetch();
	return $result["configvalue"];
	}

	 //--------------------------------------- 
    // convert custom options to json
	// return json in fileoptions
    // ---------------------------------------
    // 
	public function customOptions($itemdata)
	{
		global $config;
		$options = array();
		 	
		$options["vlts"] = (isset($itemdata["vlts"]) && $itemdata["vlts"])?true:false;	// voucher locked to return to sender only 
		
		// add options to data	
		$itemdata["fileoptions"] = json_encode($options);
		return $itemdata;
	}	
	
		//--------------------------------------- 
    // check if string is json
    // Returns bool true/false
	// ---------------------------------------
	 public function isJson($string) 
	 {
		 json_decode($string);
		 return (json_last_error() == JSON_ERROR_NONE);
	}
}
?>
