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
//change back to _POST from _REQUEST

	if (isset($_POST['call'])) {
		$call = htmlspecialchars($_POST['call']);
		
	if ($call == "logon" ) {
		echo $authsaml->logonURL();
		exit;
	}
	
	if ($call == "logoff" ) {
		echo $authsaml->logoffURL();
		exit;
	}
	
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
	
	case "log_process":
    	echo $log->logProcess($_POST['client'],$_POST['message']);
    	break;
	
	case "getAuth":
        echo $authsaml->isAuth();
        break;
		
	case "getAuthAdmin":
        echo $authsaml->authIsAdmin();
        break;
		
	case "moveFile":
        echo $functions->moveFile();
        break;
		
	case "getFileSize":
        echo $functions->getFileSize($_POST['filename']);
        break;
		
	case "completeFile":
        echo $functions->updateFile();
        break;
		
	case "downloadedFile":
        echo $functions->downloadedFile();
        break;
		
	case "driveSpace":
        echo $functions->driveSpace();
        break;

	case "getAdminLogs":
        echo $functions->adminLogs();
	    break;
		
	case "getAdminFiles":
        echo $functions->adminFiles();
		break;
		
    case "fileInfo":
		$jsonString = rawurldecode($_POST['jsonSendData']);
  		//$jsonString = str_replace("\\", "", $jsonString);
  		$data = json_decode($jsonString, true);
        echo $functions->getFile($data);
        break;
		
	case "getFile":
		echo $authvoucher->getVoucher();
        break;
		
	case "getVouchers":
		echo $functions->getVouchers();
		break;
		
		case "getUserFiles":
		echo $functions->getUserFiles();
		break;
		
	case "deleteVoucher": 
		echo $functions->deleteVoucher($_POST['svid']);
		break;
	
	case "deleteFile": 
		echo $functions->deleteFile($_POST['svid']);
		break;
		
	case "closeVoucher": 
		echo $functions->closeVoucher($_POST['svid']);
		break;
		
	case "resendVoucher":
		$fileArray =  $functions->getVoucher($_POST['svid']);
		
		echo $sendmail->sendEmail($fileArray[0],$config['voucherissuedemailbody']);

		break;
		
	case "resendFile":
		$fileArray =  $functions->getVoucher($_POST['svid']);
		
		echo $sendmail->sendEmail($fileArray[0],$config['fileuploadedemailbody']);

		break;
		
    case "updateFile":
		echo $functions->updateFile();
		break;
		
	case "insertFile":
		echo $functions->insertFile();
		break;
		
	}

	}
	}

	 

?>
