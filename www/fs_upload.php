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

/* ---------------------------------
 * upload using html5
 * ---------------------------------
 * data is sent in chunks from html and appended to the file folder
 * store current file information in $filedata[];
 */
// use token if available for SIMPLESAML 1.7 or set session if earlier version of SIMPLESAML
if (isset($_POST['token']) && $_POST['token'] != "") {
	$_COOKIE['SimpleSAMLAuthToken'] = $_POST['token'];
}	
	// flash upoload creates a new session id https so we need to make sure we are using the same session  
if(!empty($_POST['s'])) { 
    session_id($_POST['s']); 
    session_start();

    // Ensure existing session, users don't have the permission to create
    // a session because that would be a security vulnerability.
    if (!isset($_SESSION['validSession'])) {
        session_destroy();
        session_start();
        session_regenerate_id();
        $_SESSION['validSession'] = true;
        trigger_error("Invalid session supplied.", E_USER_ERROR);
    }
}
	
require_once('../classes/_includes.php');

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$log =  Log::getInstance();
$functions = Functions::getInstance();

date_default_timezone_set($config['Default_TimeZone']);
$uploadfolder =  $config["site_filestore"];
	
logEntry("DEBUG fs_upload: REQUEST data: " . print_r($_REQUEST, true));
logEntry("DEBUG fs_upload: POST data: " . print_r($_POST, true));

// check we are authenticated first before uploading the chunk
if($authvoucher->aVoucher()  || $authsaml->isAuth() ) { 

	// tempFilename is created from ((uid or vid)+originalfilename+filesize)
	$tempFilename = ""; 
	 
	// add voucher if this is a voucher upload
	if ($authvoucher->aVoucher()) {
	
		$tempFilename .= $_REQUEST['vid'];
		$tempData = $functions->getVoucherData($_REQUEST["vid"]);
		$filedata["fileauthuseruid"] = $tempData[0]["fileauthuseruid"];	
		$filedata["fileauthuseremail"] = $tempData[0]["fileauthuseremail"];	
		logEntry("DEBUG fs_upload: tempfilename 1v : ".$tempFilename);
	}
	// else add SAML saml_uid_attribute
	else if( $authsaml->isAuth()) {
		$authAttributes = $authsaml->sAuth();
		$tempFilename .= $authAttributes["saml_uid_attribute"];	
		$filedata["fileauthuseruid"] = $authAttributes["saml_uid_attribute"];
		$filedata["fileauthuseremail"] = $authAttributes["email"];
		logEntry("DEBUG fs_upload: tempfilename 1a : ".$tempFilename);
	} 
	
	// add the file name
	if(isset($_REQUEST['filename'])){
	$tempFilename .=  sanitizeFilename($_REQUEST['filename']);
	logEntry("DEBUG fs_upload: tempfilename 2 : ".$tempFilename);
	}
	// add the file size to the filename
	if(isset($_REQUEST['filesize'])){
	$tempFilename .=  $_REQUEST['filesize'];
	logEntry("DEBUG fs_upload: tempfilename 3 : ".$tempFilename);
	}
	// md5 $tempFilename
	$tempFilename = md5($tempFilename).'.tmp';
	logEntry("DEBUG fs_upload: tempfilename 4 : ".$tempFilename);

	 
	if ( !empty( $tempFilename ) ) {
	// ---------------------
	// return file size if requested
	// ---------------------
	if(isset($_REQUEST["type"])){

	//logEntry("type ".$config["site_filestore"].sanitizeFilename($tempFilename).":".$_REQUEST["type"]);
		
	switch ($_REQUEST["type"]) {
			
	case 'filesize':
		echo checkFileSize($uploadfolder.$tempFilename);
		break;
		
	case 'uploadcomplete': 
		// change each file from pending to done
		break;
		
	case  'validateupload':
	// validates form and adds pending file to files, returns filesize or validation message
	
			$dataitem = json_decode(stripslashes($_POST['myJson']), true);
			// validate date selector
			if(!isset($dataitem["filesize"])){ echo "err_missingfilesize"; exit; }
			// check drive space for file upload
			if(disk_free_space($config['site_filestore']) - $dataitem["filesize"] < 1) {echo "err_nodiskspace"; exit; } // use absolute locations result in bytes
			// validate expiry missing
			if(!isset($dataitem["fileexpirydate"])){ echo "err_expmissing"; exit; }
			// validate fileto missing
			if(!isset($dataitem["fileto"])){ echo "err_tomissing"; exit;}
			// validate expiry range
			if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($dataitem["fileexpirydate"]) < strtotime("now"))
			{ echo "err_exoutofrange"; exit; }
			// seperate emails
			$emailto = str_replace(",",";",$dataitem["fileto"]);
			$emailArray = preg_split("/;/", $emailto);
			// validate number of emails
			if(count($emailArray) > $config['max_email_recipients'] ) {echo "err_toomanyemail"; exit;}
			// validate individual emails
			foreach ($emailArray as $Email) {
			if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) {echo "err_invalidemail"; exit;}
			}
			// if AUP then add session variable to store that a user selected the session variable
			if(isset($_POST["aup"]))
			{
				$_SESSION["aup"] = "true";
			}
	
			echo checkFileSize($uploadfolder.$tempFilename);
			break;
			
	case 'single':
	
	// ---------------------
	// single file upload
	// ---------------------	
	
		
		 $result = move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfolder.$tempFilename);
		 if($result) {
			logEntry("DEBUG fs_upload.php: file moved:". $_FILES['Filedata']['tmp_name'] . " <- ".$tempFilename );
			echo "true";
		 } else {
			logEntry("DEBUG fs_upload.php: file NOT moved:". $_FILES['Filedata']['tmp_name'] . " <- ".$tempFilename );
			echo "false";
		 }
		 break;
		 
	case 'chunk': 
	// ---------------------	
	// CHUNK file upload
	// ---------------------	
	// open the temp file
		
		$fd = fopen("php://input", "r");
		// append the chunk to the temp file
		while( $data = fread( $fd,  1000000  ) ) file_put_contents( $config["site_filestore"].sanitizeFilename($tempFilename), $data, FILE_APPEND ) or die("Error");
		// close the file 
		fclose($fd);
		logEntry("Uploaded ".$config["site_filestore"].sanitizeFilename($tempFilename));
		// return file size
		echo checkFileSize($uploadfolder.$tempFilename);
		break;
	
	case 'savedata': 
	
	// validate and save data to db
		$fileuid = getGUID();
		// rename file to correct name
		logEntry("Rename the file ".$uploadfolder.$tempFilename+":"+ $uploadfolder.$fileuid.".tmp");
        $result = rename($uploadfolder.$tempFilename, $uploadfolder.$fileuid.".tmp");
        if(!$result) {
                logEntry("Unable to move the file ".$uploadfolder.$tempFilename);
                trigger_error("Unable to move the file", E_USER_ERROR);
        } else {
			    logEntry("Rename the file ".$uploadfolder.$fileuid.".tmp");
		}

	$filedata = json_decode(stripslashes($_POST['myJson']), true);
	logEntry("DEBUG fs_uploadit: Filedata 'savedata' = " . $filedata);
	if ($authvoucher->aVoucher()) {
		$tempData = $functions->getVoucherData($filedata["filevoucheruid"]);
		$filedata["fileauthuseruid"] = $tempData[0]["fileauthuseruid"];	
		$filedata["fileauthuseremail"] = $tempData[0]["fileauthuseremail"];	
	} else if( $authsaml->isAuth()) {
		$authAttributes = $authsaml->sAuth();
		$filedata["fileauthuseruid"] = $authAttributes["saml_uid_attribute"];
		$filedata["fileauthuseremail"] = $authAttributes["email"];
	}
	// close current file if a voucher
	if(isset($filedata["filestatus"]) && $filedata["filestatus"] == "Voucher")
	{
	logEntry("DEBUG fs_uploadit: Close Voucher = " . $filedata["filevoucheruid"]);	
	$tempData = $functions->getVoucherData($filedata["filevoucheruid"]);
	$functions->closeVoucher($tempData[0]["fileid"]);
    }
		
	$filedata["fileuid"] = $fileuid;
	$filedata["filestatus"]  = "Available";
	$filedata["fileexpirydate"] = date($config["db_dateformat"],strtotime($filedata["fileexpirydate"]));
	
	// loop though multiple emails
	$emailto = str_replace(",",";",$filedata["fileto"]);
	$emailArray = preg_split("/;/", $emailto);
	foreach ($emailArray as $Email) { 
	$filedata["fileto"] = $Email;
	$filedata["filevoucheruid"] = getGUID();
	
	logEntry("DEBUG fs_uploadit: Filedata = " . $filedata);
	$functions->insertFileHTML5($filedata);
	}
	echo "true";

	break;

	case 'insertVoucherAjax': 

			$dataitem = json_decode(stripslashes($_POST['myJson']), true);
			// validate expiry missing
			if(!isset($dataitem["fileexpirydate"])){ echo "err_expmissing"; exit; }
			// validate fileto missing
			if(!isset($dataitem["fileto"])){ echo "err_tomissing"; exit;}
			// validate expiry range
			if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($dataitem["fileexpirydate"]) < strtotime("now")) { echo "err_exoutofrange"; exit; }
			// seperate emails
			$emailto = str_replace(",",";",$dataitem["fileto"]);
			$emailArray = preg_split("/;/", $emailto);
			// validate number of emails
			if(count($emailArray) > $config['max_email_recipients'] ) {echo "err_toomanyemail"; exit;}
			// validate individual emails
			foreach ($emailArray as $Email) {
			if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) {echo "err_invalidemail"; exit;}
			}
			// insert each voucher
			foreach ($emailArray as $Email) { 
			$functions->insertVoucher($Email,$dataitem["fileexpirydate"]);
			} 
			echo "complete";
			break;
	}
	} else {
	// log and return errorAuth if not authenticated
	logEntry("fs_upload.php: Error authorising upload :Voucher-".$authvoucher->aVoucher().":SAML-". $authsaml->isAuth());
	echo "ErrorAuth";
	}
}
}
	
function checkFileSize($fileLocation)
{
if (file_exists($fileLocation)) {
		//We should turn this into a switch/case, exhaustive with a default case
		if (PHP_OS == "Darwin") {
            $size = trim(shell_exec("stat -f %z ". escapeshellarg($fileLocation)));
		}
		else if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
    	{
        	$size = trim(shell_exec("stat -c%s ". escapeshellarg($fileLocation)));
    	}	
		else { 
                 $fsobj = new COM("Scripting.FileSystemObject"); 
                 $f = $fsobj->GetFile($fileLocation); 
                 $size = $f->Size; 
        }
		return $size;
	} else {

	return 0;
	}
}
?>
