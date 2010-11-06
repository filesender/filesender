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

// check we are authenticated first before uploading the chink
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
	logEntry("DEBUG fs_gears_upload: Using ". $tempFilename . "(vid/uid)");
	
	// add the file name
	$tempFilename .=  sanitizeFilename($_GET['n']);
	logEntry("DEBUG fs_gears_upload: Using ". $tempFilename . "(filename)");

	// add the file size to the filename
	$tempFilename .=  $_GET['total'];
	logEntry("DEBUG fs_gears_upload: Using ". $tempFilename . "(filesize)");

	// md5 $tempFilename
	$tempFilename = md5($tempFilename).'.tmp';
	logEntry("DEBUG fs_gears_upload: Using ". $tempFilename . "(md5)");

	 
	if ( !empty( $tempFilename ) ) {
		// open the temp file
		$fd = fopen("php://input", "r");
		// append the chunk to the temp file
		while( $data = fread( $fd,  1000000  ) ) file_put_contents( $config["site_temp_filestore"].sanitizeFilename($tempFilename), $data, FILE_APPEND ) or die("Error");
		// close the file 
		fclose($fd);
	}

} else {
	// log and return errorAuth if not authenticated
	logEntry("Error authorising Gears upload :Voucher-".$authvoucher->aVoucher().":SAML-". $authsaml->isAuth());
	echo "ErrorAuth";

}

?>
