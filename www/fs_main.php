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
 
 /*
  * flex calls fs_main.php for all requests except the actual file uploading
  * $_POST['call'] defines the functions triggere from this call
  * flex uses HTTPService calls to talk to php and returns results as text or json text
  * e.g. <mx:HTTPService id="init_main" url="../fs_main.php{URLvid}" useProxy="false" method="POST" resultFormat="text" result="resultInit(event)" fault="resultError(event)"/> 
  */

require_once('../classes/_includes.php');
$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$functions = Functions::getInstance();
$lang = EN_AU::getInstance();
$CFG = config::getInstance();
$config = $CFG->loadConfig();
$sendmail = Mail::getInstance();
$log = Log::getInstance();

$returnData = array(); // init return array

// set time zone for this session
date_default_timezone_set($config['Default_TimeZone']);

if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	session_start();
	$_SESSION['validSession'] = true;
} 
// test if flex can continue to call this page
// check if valid vourcher

// get the function call from flex
	if (isset($_POST['call'])) {
		$call = htmlspecialchars($_POST['call']);
		
	// call logon function
	if ($call == "logon" ) {
		echo $authsaml->logonURL();
		exit;
	}
	
	// call logoff function
	if ($call == "logoff" ) {
		echo $authsaml->logoffURL();
		exit;
	}
	
	// return init/configuration data as array to flex
	if($call == "getInit") {
	
	$returnData["lang"] = json_encode($lang->language());
	$returnData["samlauth"] = $authsaml->isAuth();
	$returnData["voucherauth"] = $authvoucher->aVoucher();
	$returnData["splash"] = $functions->getSplash();
	$returnData["statsdata"] = "";
	$returnData["authIsAdmin"] = $authsaml->authIsAdmin();
	if($authvoucher->aVoucher() || $authsaml->isAuth()) 
	{
		$returnData["statsdata"] = $functions->getStats();
		$returnData["config"] = $functions->getConfig();
		
		if($authsaml->isAuth()) 
		{
			$returnData["authUser"]  =  json_encode($authsaml->sAuth());
			
		}
	
		if($authvoucher->aVoucher()) 
		{
			$returnData["voucher"]  =  $authvoucher->validVoucher();
		}
			
		
 	}
		//$sendmail->sendemailAdmin(json_encode($returnData));
		echo json_encode($returnData); 
	}
	
	if(!$authvoucher->aVoucher() && !$authsaml->isAuth()) {
		echo "notAuthenticated";
 	}
	else 
	{
	// process requests from flex
	
	switch ($call) {
	
	// log data from flex
	case "log_process":
    	echo $log->logProcess($_POST['client'],$_POST['message']);
    	break;
	
	//  return authentication for flex
	case "getAuth":
        echo $authsaml->isAuth();
        break;
	
	// return true if users is admin
	case "getAuthAdmin":
        echo $authsaml->authIsAdmin();
        break;
	
	// request a file move from flex	
	case "moveFile":
        echo $functions->moveFile();
        break;
	
	// return filesize for flex to calulcate gears or compare uploaded file	
	case "getFileSize":
        echo $functions->getFileSize($_POST['filename']);
        break;
	
	// updates voucher/file data when there is a change in the voucher data
	case "completeFile":
        echo $functions->updateFile();
        break;
		
	// email and log a file when downloaded
	case "downloadedFile":
        echo $functions->downloadedFile();
        break;
	
	// resturn drivespace as array site_filestore_total; site_temp_filestore_total;	site_filestore_free; site_temp_filestore_free
	case "driveSpace":
        echo $functions->driveSpace();
        break;
		
	// returns json array of admin log data from log table
	case "getAdminLogs":
        echo $functions->adminLogs();
	    break;
	
	// returns json array of all uploaded files for admin
	case "getAdminFiles":
        echo $functions->adminFiles();
		break;
	
	// returns  file information as json array
	case "fileInfo":
		$jsonString = rawurldecode($_POST['jsonSendData']);
		$jsonString = utf8_encode($jsonString);
		$data = json_decode($jsonString, true);
		echo $functions->getFile($data);
		break;
	
	// returns voucher information as json array for flex
	case "getFile":
		echo $authvoucher->getVoucher();
        break;
	
	// resturns all vouchers for a specific user as json array for flex	
	case "getVouchers":
		echo $functions->getVouchers();
		break;
		
	// resurns current users file data as json array for flex
	case "getUserFiles":
		echo $functions->getUserFiles();
		break;
		
	// deletes a specific voucher	
	case "deleteVoucher": 
		echo $functions->deleteVoucher($_POST['svid']);
		break;
	
	// remove a specific file by voucherid
	case "deleteFile": 
		echo $functions->deleteFile($_POST['svid']);
		break;
	
	// lcoses a specific voucher by voucherid
	case "closeVoucher": 
		echo $functions->closeVoucher($_POST['svid']);
		break;
	
	// resends voucher as requested by flex in myFiles	
	case "resendVoucher":
		$fileArray =  $functions->getVoucher($_POST['svid']);
		
		echo $sendmail->sendEmail($fileArray[0],$config['voucherissuedemailbody']);

		break;
	
	// resend file	as requested by flex in myFiles
	case "resendFile":
		$fileArray =  $functions->getVoucher($_POST['svid']);
		
		echo $sendmail->sendEmail($fileArray[0],$config['fileuploadedemailbody']);

		break;
	
	// insert a new file or voucher
	case "insertFile":
		
		logEntry("INSERT:".rawurldecode($_POST['jsonSendData']));
		echo $functions->insertFile();
		break;
		
	}

	}
	}

	 

?>
