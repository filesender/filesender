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
 *
 * ------------------------------
 * Upload file from flex application and move into site_filestore folder
 * ------------------------------
 * returns string: moveOk, moveError,invalidAuth back to flex
 */
function upperHexNumber($matches) {
    return '\u'.strtoupper($matches[1]);
}

require_once('../classes/_includes.php');

// flash upoload creates a new session id https so we need to make sure we are using the same session  
if(!empty($_REQUEST['s'])) { 
    session_id($_REQUEST['s']); 
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

// Check if there is a file supplied
if(!isset($_FILES['fileToUpload'])) trigger_error("request without file upload", E_USER_ERROR);

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$CFG = config::getInstance();
$cofig = $CFG->loadConfig();
$functions = Functions::getInstance();
date_default_timezone_set($config['Default_TimeZone']);

if($authvoucher->aVoucher() || $authsaml->isAuth()) { 
    $uploadfolder =  $config["site_filestore"];
	
	$fileuid = getGUID();
    $filesize = $_FILES['fileToUpload']['size'];

	$correctfilename = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/','upperHexNumber',trim(json_encode($_FILES['fileToUpload']['name']),"\""));

    // move file to correct uploadfolder destination
    $result = move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadfolder.$fileuid.".tmp");
	
	$filedata["filefrom"] = $_POST["filefrom"];
	$filedata["filesize"] = $filesize;
	$filedata["filesubject"] = $_POST["filesubject"];
	$filedata["filemessage"] = $_POST["filemessage"];
	$filedata["fileoriginalname"] =  $correctfilename;
	$filedata["fileuid"] = $fileuid;
	$filedata["filestatus"]  = "Available";
	$filedata["fileexpirydate"] = date($config["postgresdateformat"],strtotime($_POST["fileexpirydate"]));
	
	// loop though multiple emails
	$emailto = str_replace(",",";",$_POST["fileto"]);
	$emailArray = preg_split("/;/", $emailto);
	foreach ($emailArray as $Email) { 
	$filedata["fileto"] = $Email;
	$filedata["filevoucheruid"] = getGUID();
	
	$functions->inserFileHTML5($filedata);
	}
	
    if($result) {
	
	if(isset($_POST["filestatus"]) && $_POST["filestatus"] == "Voucher")
		{
		$tempData = $functions->getVoucherData($_POST["filevoucheruid"]);
		$functions->closeVoucher($tempData[0]["fileid"]);	
        }
		
		if(isset($_POST["loadtype"]) && $_POST["loadtype"] == "standard")
		{
		$redirect = "index.php?s=complete";
		echo('<script type="text/javascript">parent.completeupload();</script>');
		}
		
        echo "moveOk";
		logEntry("File Moved");
		// close voucher
		
    } else {
	
        // error moving files
		echo "moveError";
        logEntry("Unable to move the file");
        
    }
} else {
	    echo "invalidAuth";
    logEntry("Error authorising Flash upload :Voucher-".$authvoucher->aVoucher().":SAML-". $authsaml->isAuth());

}
?>
