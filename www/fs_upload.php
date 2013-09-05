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

/* ---------------------------------
 * upload using html5 or flash
 * ---------------------------------
 * data is sent in chunks from html and appended to the file folder
 * store current file information in $filedata[];
 * all data sent to this page must include ?vid= or be an authenticated user
 * 
 */
// set cache to default - nocache
session_cache_limiter('nocache');
// use token if available for SIMPLESAML 1.7 or set session if earlier version of SIMPLESAML
if (isset($_POST['token']) && $_POST['token'] != "") {
    $_COOKIE['SimpleSAMLAuthToken'] = htmlspecialchars($_POST['token']);
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
} else {
    session_start();
}

require_once('../classes/_includes.php');

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$log =  Log::getInstance();
$functions = Functions::getInstance();

date_default_timezone_set($config['Default_TimeZone']);
$uploadfolder =  $config["site_filestore"];
$resultArray = array();
$errorArray = array();

logEntry("DEBUG fs_upload: magic_quotes_gpc=".get_magic_quotes_gpc());
logEntry("DEBUG fs_upload: REQUEST data: " . print_r($_REQUEST, true));
logEntry("DEBUG fs_upload: POST data: " . print_r($_POST, true));
logEntry("DEBUG fs_upload: SESSION data: " . print_r($_SESSION, true));

// check we are authenticated first before continuing
if(($authvoucher->aVoucher()  || $authsaml->isAuth()) && isset($_REQUEST["type"]))
{
    // check if post and s-token is valid
    require('../includes/XSRF.php');

    // tempFilename is created from ((uid or vid)+originalfilename+filesize)
    $tempFilename = "";

    switch ($_REQUEST["type"]) {

        // Get the current file size based on the voucher data
        case 'filesize':
            $data = $functions->getVoucherData($_REQUEST["vid"]);
            $tempFilename = generateTempFilename($data);
            echo checkFileSize($uploadfolder.$tempFilename);
            break;

        // Finish an upload (called after a validateupload and single/chunk sequence)
        case 'uploadcomplete':

            // change each file from pending to done
            $data = $functions->getVoucherData($_REQUEST["vid"]);
            $tempFilename = generateTempFilename($data);
            $complete = "complete";

            // rename file to correct name
            $fileuid = getGUID();

            // close pending file
            $functions->closeVoucher($data["fileid"]);

            // error if file size uploaded doesn't matches the file size intended to upload
            // remove the offending file or it will assume resume evry re-attempt
            if($data["filesize"] != checkFileSize($uploadfolder.$tempFilename))
            {
                logEntry("DEBUG fs_upload: File size incorrect after upload = Original:" .$data["filesize"] . " != Actual:". checkFileSize($uploadfolder.$tempFilename), "E_ERROR" );
                if(file_exists($uploadfolder.$tempFilename))
                {
                    unlink($uploadfolder.$tempFilename);
                    logEntry("DEBUG fs_upload: File  ".$tempFilename." was removed to prevent resume", "E_ERROR");
                }
                array_push($errorArray,  "err_filesizeincorrect");
                returnerrorandclose();
            }

            logEntry("Rename the file ".$uploadfolder.$tempFilename+":"+ $uploadfolder.$fileuid.".tmp");
            if (!file_exists($uploadfolder.$tempFilename)) {
                array_push($errorArray,  "err_cannotrenamefile");
                returnerrorandclose();
            }

            if(!rename($uploadfolder.$tempFilename, $uploadfolder.$fileuid.".tmp")) {
                array_push($errorArray,  "err_cannotrenamefile");
                logEntry("Unable to move the file ".$uploadfolder.$tempFilename, "E_ERROR");
                returnerrorandclose();
            } else {
                logEntry("Rename the file ".$uploadfolder.$fileuid.".tmp");
            }

            // voucher has been used so close it
            if (isset($_SESSION['voucher'])) {
                $functions->closeCompleteVoucher($_SESSION['voucher']);
                logEntry("DEBUG fs_upload: Close voucher = " . $_SESSION['voucher']);
                $_SESSION['voucher'] = NULL;
                $_SESSION["aup"] = NULL;
                $complete = "completev";
            }

            $data["fileuid"] = $fileuid;
            $data["filestatus"]  = "Available";
            $data["fileexpirydate"] = date($config["db_dateformat"],strtotime($data["fileexpirydate"]));

            // loop though multiple emails
            // TO DO: must error check here if emails do not send or fails with data insertion
            $emailto = str_replace(",",";",$data["fileto"]);
            $emailArray = preg_split("/;/", $emailto);
            foreach ($emailArray as $Email) {
                $data["fileto"] = $Email;
                $data["filevoucheruid"] = getGUID();

                logEntry("DEBUG fs_upload: Filedata = " . print_r($data,TRUE));
                $functions->insertFile($data);

            }
            if(sizeof($errorArray) > 0 ) { $resultArray["errors"] =  $errorArray; }
            $resultArray["status"] = $complete;
            $resultArray['gid'] = $data['filegroupid'];
            echo json_encode($resultArray);
            break;

        // validates form and adds pending file to files, returns filesize or validation message
        case  'validateupload':
            logEntry("DEBUG fs_upload: Filedata 'validateupload' myJson = " . $_POST['myJson'] );
            $dataitem = json_decode($_POST['myJson'], true);
            if(!isset($dataitem["fileuid"]))
            {
                $dataitem["fileuid"] = getGUID();
            }

            if ($authvoucher->aVoucher()) {
                $tempData = $functions->getVoucherData($_REQUEST["vid"]);
                $dataitem["fileauthuseruid"] = $tempData["fileauthuseruid"];
                $dataitem["fileauthuseremail"] = $tempData["filefrom"];
                $dataitem["fileuid"] = $_REQUEST["vid"];
            } else if( $authsaml->isAuth()) {
                $authAttributes = $authsaml->sAuth();
                $dataitem["fileauthuseruid"] = $authAttributes["saml_uid_attribute"];
                $dataitem["fileauthuseremail"] = $dataitem["filefrom"];
                $dataitem["fileuid"] = md5($authAttributes["saml_uid_attribute"]);
            }

            // if AUP then add session variable to store that a user selected the session variable
            if(isset($dataitem["aup"]))
            {
                $_SESSION["aup"] = "true";
            }

            // voucher has been used so add a SESSION variable
            if ($authvoucher->aVoucher()) {
                $_SESSION['voucher'] = $_REQUEST['vid'];
            }

            $dataitem = $functions->validateFileData($dataitem);

            // check if this is a pending upload so that we can continue
            $tempFilename = generateTempFilename($dataitem);
            $pendingData = $functions->checkPending($dataitem);
            if($pendingData != "" && $tempFilename == generateTempFilename($pendingData))
            {
                $dataitem["filevoucheruid"] = $pendingData["filevoucheruid"];
                $functions->updateFile($dataitem);
            } else {
                $dataitem["filevoucheruid"] = getGUID();
                $dataitem["filestatus"] = "Pending";
                $functions->insertFile($dataitem);
            }

            $resultArray["filesize"] = checkFileSize($uploadfolder.$tempFilename);
            $resultArray["vid"] = $dataitem["filevoucheruid"];
            $resultArray["status"] = "complete";
            echo json_encode($resultArray);

            break;

        case 'single':
            // ----------------------------------
            // single file upload used with Flash
            // ----------------------------------

            $data = $functions->getVoucherData($_REQUEST["vid"]);
            $tempFilename = generateTempFilename($data);
            $result = move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfolder.$tempFilename);
            if($result) {
                logEntry("DEBUG fs_upload.php: file moved:". $_FILES['Filedata']['tmp_name'] . " <- ".$tempFilename );
                echo "true";
            } else {
                logEntry("DEBUG fs_upload.php: file NOT moved:". $_FILES['Filedata']['tmp_name'] . " <- ".$tempFilename, "E_ERROR" );
                echo "false";
            }
            break;

        // ---------------------------------
        // CHUNK file upload used with HTML5
        // ---------------------------------
        case 'chunk':
            // open the temp file
            $data = $functions->getVoucherData($_REQUEST["vid"]);
            $tempFilename = generateTempFilename($data);

            $fd = fopen("php://input", "r");
            // append the chunk to the temp file
            while( $data = fread( $fd,  1000000  ) ) file_put_contents( $config["site_filestore"].sanitizeFilename($tempFilename), $data, FILE_APPEND ) or die("Error");
            // close the file
            fclose($fd);
            logEntry("Uploaded ".$config["site_filestore"].sanitizeFilename($tempFilename));
            // return file size
            echo checkFileSize($uploadfolder.$tempFilename);
            break;

        // Insert a new guest invite (voucher)
        case 'insertVoucherAjax':
            $complete = "";
            $errorArray = array();
            // check authenticated first :NOTE:
            if( $authsaml->isAuth()) {
                logEntry("DEBUG fs_upload: Filedata 'insertVoucherAjax' myJson = " . $_POST['myJson'] );
                $dataitem = json_decode($_POST['myJson'], true);
                // validate expiry missing
                if(!isset($dataitem["fileexpirydate"])){ array_push($errorArray,  "err_expmissing"); }
                // validate fileto missing
                if(!isset($dataitem["fileto"])){  array_push($errorArray,  "err_tomissing");}
                // validate expiry range
                // Don't generate a validation error but fix the expiry date to correct timezone/clock skew mishaps
                if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($dataitem["fileexpirydate"]) < strtotime("now")) {
                    $dataitem["fileexpirydate"] = $functions->ensureValidFileExpiryDate($dataitem["fileexpirydate"]);
                    /* echo "err_exoutofrange"; exit; */
                }
                // seperate emails
                $emailto = str_replace(",",";",$dataitem["fileto"]);
                $emailArray = preg_split("/;/", $emailto);
                // validate number of emails
                if(count($emailArray) > $config['max_email_recipients'] ) { array_push($errorArray,  "err_toomanyemail"); }
                // validate individual emails
                foreach ($emailArray as $Email) {
                    if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) { array_push($errorArray,  "err_invalidemail"); }
                }
                if(sizeof($errorArray) > 0 ) {
                    $resultArray["errors"] =  $errorArray;
                } else {
                    // insert each voucher
                    foreach ($emailArray as $Email) {
                        $functions->insertVoucher($Email,$dataitem['filefrom'],$dataitem["fileexpirydate"],$dataitem["vouchermessage"],$dataitem["vouchersubject"]);
                    }
                    $complete = "complete";
                }
            } else {
                $complete =  "not_authenticated";
            }
            $resultArray["status"] = $complete;
            echo json_encode($resultArray);
            break;

        // insert add new recipient to existing file
        case 'addRecipient':
            $errorArray = array();
            // test
            //array_push($errorArray, "err_invalidemail");
            $dataitem = json_decode($_POST['myJson'], true);
            $myfileData = $functions->getVoucherData($dataitem["filevoucheruid"]);
            $myfileData["filecreateddate"] = date($config['db_dateformat'], time());
            $myfileData["filemessage"] = $dataitem["filemessage"];
            $myfileData["filesubject"] = $dataitem["filesubject"];
            $myfileData["fileexpirydate"] = date($config["db_dateformat"],strtotime($dataitem["fileexpirydate"]));
            // validate fileto and fileexpiry
            // expiry missing
            if(!isset($dataitem["fileexpirydate"])){ array_push($errorArray,  "err_expmissing"); }
            // expiry out of range
            // Don't generate a validation error but fix the expiry date to correct timezone/clock skew mishaps
            if(strtotime($dataitem["fileexpirydate"]) > strtotime("+".$config['default_daysvalid']." day") ||  strtotime($dataitem["fileexpirydate"]) < strtotime("now"))
            {
                $myfileData["fileexpirydate"] = $functions->ensureValidFileExpiryDate($myfileData["fileexpirydate"]);
                /* array_push($errorArray,"err_exoutofrange"); */
            }
            // emmail missing
            if(!isset($dataitem["fileto"]))
            {
                array_push($errorArray,  "err_filetomissing");
            } else {
                $emailto = str_replace(",",";",$dataitem["fileto"]);
                $emailArray = preg_split("/;/", $emailto);
                // validate number of emails
                if(count($emailArray) > $config['max_email_recipients'] ) {array_push($errorArray,  "err_toomanyemail");}
                // validate individual emails
                foreach ($emailArray as $Email) {
                    if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) {array_push($errorArray, "err_invalidemail");}
                }
            }
            if(count($errorArray) > 0 )
            {
                $resultArray["errors"] =  $errorArray;
                echo json_encode($resultArray);
                break; // NOTE: exit instead of break ???
            }

            // loop emails in fileto
            $emailto = str_replace(",",";",$dataitem["fileto"]);
            $emailArray = preg_split("/;/", $emailto);
            foreach ($emailArray as $Email) {
                $myfileData["fileto"] = $Email;
                $myfileData["filevoucheruid"] = getGUID();
                $functions->insertFile($myfileData);
            }
            // resend errors if emails produced error
            if(count($errorArray) > 0 )
            {
                $resultArray["errors"] =  $errorArray;
                echo json_encode($resultArray);
                break; // NOTE: exit instead of break ???
            }

            $resultArray["status"] = "complete";
            echo json_encode($resultArray);

            break;
    } // End switch
} else {
    // log and return errorAuth if not authenticated
    logEntry("fs_upload.php: Error authorising upload :Voucher-".$authvoucher->aVoucher().":SAML-". $authsaml->isAuth(), "E_ERROR");
    echo "ErrorAuth";
}

function returnerrorandclose()
{
    global $resultArray, $errorArray;
    $resultArray["errors"] =  $errorArray;
    echo json_encode($resultArray);
    exit;
}

function generateTempFilename($data)
{
    $authsaml = AuthSaml::getInstance();
    $authvoucher = AuthVoucher::getInstance();
    $functions = Functions::getInstance();
    $tempFilename= "";

    // Add Guest Voucher id if a voucher is used
    if(isset($_SESSION['voucher']))
    {
        $tempFilename .= $_SESSION['voucher'];
        logEntry("DEBUG fs_upload: tempfilename 1v1 : ".$tempFilename);
    }
    // else add SAML saml_uid_attribute
    else if( $authsaml->isAuth()) {
        $authAttributes = $authsaml->sAuth();
        $tempFilename .= $authAttributes["saml_uid_attribute"];
        logEntry("DEBUG fs_upload: tempfilename 1a : ".$tempFilename);
    } else if ($authvoucher->aVoucher()) {
        // should not be used anymore. Since this means there is some error with the
        // voucher upload (cancelled, used in anaother session etc) just generate an auth error.
        $tempFilename .= $_REQUEST['vid'];
        logEntry("DEBUG fs_upload: tempfilename 1v2 : ".$tempFilename);
        logEntry("DEBUG fs_upload.php: Voucher upload error: ".$_REQUEST['vid'], "E_ERROR");
        echo "ErrorAuth";
        exit;
    }

    // add the file name
    if(isset($data['fileoriginalname'])){
        $tempFilename .=  sanitizeFilename($data['fileoriginalname']);
        logEntry("DEBUG fs_upload: tempfilename 2 : ".$tempFilename);
    }
    // add the file size to the filename
    if(isset($data['filesize'])){
        $tempFilename .=  $data['filesize'];
        logEntry("DEBUG fs_upload: tempfilename 3 : ".$tempFilename);
    }
    // md5 $tempFilename
    $tempFilename = md5($tempFilename).'.tmp';
    logEntry("DEBUG fs_upload: tempfilename 4 : ".$tempFilename);

    return $tempFilename;
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
