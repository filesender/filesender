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
 * upload using gears
 * ---------------------------------
 * data is sent in chunks from google gears and appended to the file in the temporary folder
 */
require_once('../classes/_includes.php');

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$log =  Log::getInstance();

$CFG = config::getInstance();
$config = $CFG->loadConfig();

date_default_timezone_set($config['Default_TimeZone']);
$tempuploadfolder =  $config["site_temp_filestore"];

	// flash upload creates a new session id https so we need to make sure we are using the same session  
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
	
// check we are authenticated first before uploading the chunk
if($authvoucher->aVoucher()  || $authsaml->isAuth() ) { 

	// generate unique filename
	// tempFilename is created from ((uid or vid)+originalfilename+filesize)
	$tempFilename = ""; 

	// add voucher if this is a voucher upload
	if ($authvoucher->aVoucher()) {
		$tempFilename .= $_REQUEST['vid'];
	}
	// else add SAML eduPersonTargetedID
	else if( $authsaml->isAuth()) {
		$authAttributes = $authsaml->sAuth();
		$tempFilename .= $authAttributes["eduPersonTargetedID"];	
	} 
	
	// add the file name
	$tempFilename .=  sanitizeFilename($_REQUEST['n']);

	// add the file size to the filename
	$tempFilename .=  $_REQUEST['total'];

	// md5 $tempFilename
	$tempFilename = md5($tempFilename).'.tmp';

	 
	if ( !empty( $tempFilename ) ) {
	
	// ---------------------
	// return file size if requested
	// ---------------------
	if(isset($_REQUEST["type"]) && $_REQUEST["type"] == "filesize")
	{
		echo checkFileSize($tempuploadfolder.$tempFilename);
	}
	// ---------------------
	// single file upload
	// ---------------------	
	if(isset($_REQUEST["type"]) && $_REQUEST["type"] == "single")
	{
	
		
		 $result = move_uploaded_file($_FILES['Filedata']['tmp_name'], $tempuploadfolder.$tempFilename);
		 if($result) {
			echo "true";
		 } else {
			echo "false";
		 }
	} 
	if(isset($_REQUEST["type"]) && $_REQUEST["type"] == "chunk")
	{
		// ---------------------	
	// CHUNK file upload
	// ---------------------	
	// open the temp file
	
		$fd = fopen("php://input", "r");
		// append the chunk to the temp file
		while( $data = fread( $fd,  1000000  ) ) file_put_contents( $config["site_temp_filestore"].sanitizeFilename($tempFilename), $data, FILE_APPEND ) or die("Error");
		// close the file 
		fclose($fd);
		
		echo checkFileSize($tempuploadfolder.$tempFilename);
	
	}
	}

} else {
	// log and return errorAuth if not authenticated
	logEntry("Error authorising Gears upload :Voucher-".$authvoucher->aVoucher().":SAML-". $authsaml->isAuth());
	echo "ErrorAuth";

}

function checkFileSize($fileLocation)
{
if (file_exists($fileLocation)) {
	if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
                {
                    $size = trim(shell_exec("stat -c%s ". escapeshellarg($fileLocation)));
                } else { 
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