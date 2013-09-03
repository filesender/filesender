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
 * Multi-file upload using HTML5.
 * ---------------------------------
 * Data is sent in chunks from HTML and appended to the file folder.
 * All data sent to this page must include ?vid= or be an authenticated user.
 */

// Set cache to default - nocache.
session_cache_limiter('nocache');

// Use token if available for SimpleSAML 1.7+ or set session if earlier version of SimpleSAML.
if (isset($_POST['token']) && $_POST['token'] != '') {
    $_COOKIE['SimpleSAMLAuthToken'] = htmlspecialchars($_POST['token']);
}

// Flash upload creates a new session ID, so we need to make sure we are using the same session.
if (!empty($_POST['s'])) {
    session_id($_POST['s']);
    session_start();
    validateSession();
} else {
    session_start();
}

require_once('../classes/_includes.php');

$authSaml = AuthSaml::getInstance();
$authVoucher = AuthVoucher::getInstance();
$log = Log::getInstance();
$functions = Functions::getInstance();
$sendMail = Mail::getInstance();

global $config;
date_default_timezone_set($config['Default_TimeZone']);
$uploadFolder = $config['site_filestore'];
$resultArray = array();
$errorArray = array();

logEntry('DEBUG fs_multi_upload: magic_quotes_gpc=' . get_magic_quotes_gpc());
logEntry('DEBUG fs_multi_upload: REQUEST data: ' . print_r($_REQUEST, true));
logEntry('DEBUG fs_multi_upload: POST data: ' . print_r($_POST, true));
logEntry('DEBUG fs_multi_upload: SESSION data: ' . print_r($_SESSION, true));

if (!isAuthenticated()) {
    logEntry('fs_multi_upload.php: Error authorising upload :Voucher-' . $authVoucher->aVoucher() . ':SAML-' . $authSaml->isAuth(), 'E_ERROR');
    echo 'ErrorAuth';
} else { // Authenticated.
    require('../includes/XSRF.php'); // Check if POST and S-token are valid.

    $tempFilename = ''; // Created from ((uid or vid) + fileoriginalname + filesize)

    switch ($_REQUEST['type']) {
        case 'filesize':
            // Get the current file size based on the voucher data.
            $data = $functions->getVoucherData($_REQUEST['vid']);
            $tempFilename = generateTempFilename($data, $_REQUEST['n']);
            echo checkFileSize($uploadFolder . $tempFilename);
            break;

        case 'uploadcomplete':
            // Finish an individual file upload (called after a validateupload and single/chunk sequence).
            $resultArray = array(); // Clear result array for errors.

            // Change each file from pending to done.
            $data = $functions->getVoucherData($_REQUEST['vid']);
            $tempFilename = generateTempFilename($data, $_REQUEST['n']);
            $tempFilePath = $uploadFolder . $tempFilename;
            $complete = 'complete';

            $fileUid = getGUID(); // Rename file to correct name
            $functions->closeVoucher($data['fileid']); // Close pending file.

            $emailSettings = json_decode($_POST['myJson'], true);
            $data['filedownloadconfirmations'] = isset($emailSettings['email-inform-download']) && $emailSettings['email-inform-download'] ? 'true' : 'false';
            $data['fileenabledownloadreceipts'] = isset($emailSettings['email-enable-confirmation']) && $emailSettings['email-enable-confirmation'] ? 'true' : 'false';
            $data['filedailysummary'] = isset($emailSettings['email-inform-daily']) && $emailSettings['email-inform-daily'] ? 'true' : 'false';

            ensureFileSizesMatch($data, $uploadFolder, $tempFilename);
            renameTempFile($tempFilePath, $uploadFolder . $fileUid);
            addDatabaseRecords($data, $fileUid);

            if (sizeof($errorArray) > 0) {
                $resultArray['errors'] = $errorArray;
            }

            $resultArray['status'] = $complete;
            $resultArray['gid'] = $data['filegroupid'];
            logEntry("gid sent to multiupload.js: " . $resultArray['gid']);
            echo json_encode($resultArray);
            break;

        case 'transactioncomplete':
            // Finish a transaction (entire upload complete).
            $resultArray = array();

            $data = $functions->getMultiFileData($_REQUEST['gid']);

            $groupIdArray = array();
            $emailSettings = json_decode($_POST['myJson'], true);
            foreach ($data as &$dataItem) {
                // Update group IDs to be individual for each recipient.
                if (!isset($groupIdArray[$dataItem['fileto']])) {
                    $groupIdArray[$dataItem['fileto']] = getOpenSSLKey();
                }

                $dataItem['filegroupid'] = $groupIdArray[$dataItem['fileto']];
                $functions->updateFile($dataItem);

                $dataItem['rtnemail'] = isset($emailSettings['rtnemail']) && $emailSettings['rtnemail'] ? 'true' : 'false';
                $dataItem['senduploadconfirmation'] = isset($emailSettings['email-upload-complete']) && $emailSettings['email-upload-complete'] ? 'true' : 'false';
            }

            unset($dataItem);

            if ($data[0]['filedownloadconfirmations'] == 'true') {
                $sendMail->sendDownloadAvailable($groupIdArray);
            }

            if ($data[0]['senduploadconfirmation'] == 'true') {
                $sendMail->sendUploadConfirmation(reset($groupIdArray));
            }

            $complete = 'complete';

            if (isset($_SESSION['voucher']) && !isset($_REQUEST['morefiles'])) {
                // The voucher has been used, so close it.
                closeVoucher();
                $complete = 'completev';
            }

            $resultArray['status'] = $complete;
            $resultArray['gid'] = reset($groupIdArray); // The first group ID.
            echo json_encode($resultArray);
            logEntry("Transaction complete");

            break;

        case 'validateupload':
            // Validates form and add pending file to DB. Return filesize or validation message.
            logEntry('DEBUG fs_multi_upload: Filedata "validateupload" myJson = ' . $_POST['myJson']);
            $dataItem = json_decode($_POST['myJson'], true);

            $dataItem = setAuthUserData($dataItem);

            if (isset($dataItem['aup'])) {
                // Store AUP checkbox selection so that user does not have to select it again in the same session.
                $_SESSION['aup'] = 'true';
            }

            if ($authVoucher->aVoucher() && isset($_REQUEST['firstfile'])) {
                // A voucher is being used, add a session variable.
                $_SESSION['voucher'] = $_REQUEST['vid'];
            } else {
                unset($_SESSION['voucher']);
            }

            $dataItem = $functions->validateFileData($dataItem);

            // Check if this is a pending upload so that we can continue.
            $tempFilename = generateTempFilename($dataItem, $_REQUEST['n']);
            $dataItem = insertPendingDbRecord($dataItem, $tempFilename);

            $resultArray['filesize'] = checkFileSize($uploadFolder . $tempFilename);
            $resultArray['vid'] = $dataItem['filevoucheruid'];
            $resultArray['status'] = 'complete';

            echo json_encode($resultArray);
            break;

        case 'single':
            // Single file upload used with Flash.
            $data = $functions->getVoucherData($_REQUEST['vid']);
            $tempFilename = generateTempFilename($data, $_REQUEST['n']);
            $result = move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadFolder . $tempFilename);

            if ($result) {
                logEntry('DEBUG fs_multi_upload.php: file moved:' . $_FILES['Filedata']['tmp_name'] . ' <- ' . $tempFilename);
                echo 'true';
            } else {
                logEntry('DEBUG fs_multi_upload.php: file NOT moved:' . $_FILES['Filedata']['tmp_name'] . ' <- ' . $tempFilename, 'E_ERROR');
                echo 'false';
            }

            break;

        case 'chunk':
            // Chunk file upload used with HTML5.
            $data = $functions->getVoucherData($_REQUEST['vid']);
            $tempFilename = generateTempFilename($data, $_REQUEST['n']);

            $fd = fopen('php://input', 'r');

            // Append the chunk to the temp file.
            while ($data = fread($fd, 1000000)) {
                file_put_contents($config['site_filestore'] . sanitizeFilename($tempFilename), $data, FILE_APPEND) or die('Error');
            }

            fclose($fd);

            logEntry('Uploaded ' . $config['site_filestore'] . sanitizeFilename($tempFilename));
            echo checkFileSize($uploadFolder . $tempFilename);
            break;

        case 'tsunami':
            // Multi-threaded (out-of-order) CHUNK file upload with HTML5 Web Workers, by Edwin Schaap and René Klomp.
            require_once '../classes/Tsunami.php';

            $data = $functions->getVoucherData($_REQUEST['vid']);
            $tempFilename = generateTempFilename($data, $_REQUEST['n']);
            $fs = new Tsunami($config['site_filestore'] . sanitizeFilename($tempFilename));
            $fs->processChunk();
            break;

        case 'insertVoucherAjax':
            // Insert a new guest invite (voucher).
            $complete = '';
            $errorArray = array();

            if (!$authSaml->isAuth()) {
                $complete = 'not_authenticated';
            } else {
                logEntry('DEBUG fs_multi_upload: Filedata "insertVoucherAjax" myJson = ' . $_POST['myJson']);
                $dataItem = json_decode($_POST['myJson'], true);

                validateFields($dataItem);

                if (count($errorArray) > 0) {
                    $resultArray['errors'] = $errorArray;
                } else {
                    // Insert the voucher(s).
                    foreach ($emailArray as $email) {
                        $functions->insertVoucher($email, $dataItem['filefrom'], $dataItem["fileexpirydate"], $dataItem["vouchermessage"], $dataItem["vouchersubject"]);
                    }

                    $complete = 'complete';
                }
            }
            $resultArray['status'] = $complete;
            echo json_encode($resultArray);
            break;

        case 'addRecipient':
            // Add new recipient to existing file.
            $errorArray = array();
            $dataItem = json_decode($_POST['myJson'], true);

            $fileData = $functions->getVoucherData($dataItem['filevoucheruid']);
            $fileData['filecreateddate'] = date($config['db_dateformat'], time());
            $fileData['filemessage'] = $dataItem['filemessage'];
            $fileData['filesubject'] = $dataItem['filesubject'];
            $fileData['fileexpirydate'] = date($config['db_dateformat'], strtotime($dataItem['fileexpirydate']));

            validateFields($dataItem);

            if (count($errorArray) > 0) {
                returnErrorAndClose();
            }

            // Add database records and send email(s).
            $emailTo = str_replace(',', ';', $dataItem['fileto']);
            $emailArray = preg_split('/;/', $emailTo);

            foreach ($emailArray as $email) {
                $fileData['fileto'] = $email;
                $fileData['filevoucheruid'] = getGUID();
                $functions->insertFile($fileData);
            }

            // Resend errors if emails produced error.
            if (count($errorArray) > 0) {
                returnErrorAndClose();
            }

            $resultArray['status'] = 'complete';
            echo json_encode($resultArray);

            break;
        case 'cancelUpload':
            if ($functions->cancelUpload($_REQUEST['fileauth'], $_REQUEST['trackingcode'])) {
                $resultArray['status'] = 'complete';
            } else {
                $resultArray['status'] = 'incomplete';
            }

            echo json_encode($resultArray);

            logEntry("Upload cancelled");
            break;

    } // End switch
}

function returnErrorAndClose()
{
    global $resultArray, $errorArray;
    $resultArray['errors'] = $errorArray;
    echo json_encode($resultArray);
    exit;
}

function generateTempFilename($data, $n)
{
    global $authSaml, $authVoucher;
    $tempFilename = 'tmp-' . $n;

    if (isset($_SESSION['voucher'])) {
        // Add Guest Voucher ID if a voucher is used.
        $tempFilename .= $_SESSION['voucher'];
        logEntry('DEBUG fs_multi_upload: tempfilename 1v1 : ' . $tempFilename);
    } else {
        if ($authSaml->isAuth()) {
            // Add SAML saml_uid_attribute.
            $authAttributes = $authSaml->sAuth();
            $tempFilename .= $authAttributes['saml_uid_attribute'];
            logEntry('DEBUG fs_multi_upload: tempfilename 1a : ' . $tempFilename);
        } else {
            if ($authVoucher->aVoucher()) {
                // Should not be used anymore. Since this means there is some error with the
                // voucher upload (cancelled, used in another session etc) just generate an auth error.
                $tempFilename .= $_REQUEST['vid'];
                logEntry('DEBUG fs_multi_upload: tempfilename 1v2 : ' . $tempFilename);
                logEntry('DEBUG fs_multi_upload.php: Voucher upload error: ' . $_REQUEST['vid'], 'E_ERROR');
                echo 'ErrorAuth';
                exit;
            }
        }
    }

    // Add the file name.
    if (isset($data['fileoriginalname'])) {
        $tempFilename .= sanitizeFilename($data['fileoriginalname']);
        logEntry('DEBUG fs_multi_upload: tempfilename 2 : ' . $tempFilename);
    }

    // Add the file size to the file name.
    if (isset($data['filesize'])) {
        $tempFilename .= $data['filesize'];
        logEntry('DEBUG fs_multi_upload: tempfilename 3 : ' . $tempFilename);
    }

    // MD5 hash the file name.
    $tempFilename = md5($tempFilename) . '.tmp';
    logEntry('DEBUG fs_multi_upload: tempfilename 4 : ' . $tempFilename);

    return $tempFilename;
}

function checkFileSize($fileLocation)
{
    if (file_exists($fileLocation)) {
        return filesize($fileLocation);
    } else {
        return 0;
    }
}

function validateSession()
{
    // Ensure existing session, users don't have the permission to create
    // a session because that would be a security vulnerability.
    if (!isset($_SESSION['validSession'])) {
        session_destroy();
        session_start();
        session_regenerate_id();
        $_SESSION['validSession'] = true;
        trigger_error('Invalid session supplied.', E_USER_ERROR);
    }
}

function isAuthenticated()
{
    global $authSaml, $authVoucher;
    return ($authVoucher->aVoucher() || $authSaml->isAuth()) && isset($_REQUEST['type']);
}

function closeVoucher()
{
    global $functions;
    $functions->closeCompleteVoucher($_SESSION['voucher']);
    logEntry('DEBUG fs_multi_upload: Close voucher = ' . $_SESSION['voucher']);
    $_SESSION['voucher'] = null;
    $_SESSION['aup'] = null;
}

function renameTempFile($tempFilePath, $newFilePath)
{
    logEntry('Rename the file ' . $tempFilePath . ':' . $newFilePath . '.tmp');
    global $errorArray;

    if (!file_exists($tempFilePath)) {
        array_push($errorArray, 'err_cannotrenamefile');
        returnErrorAndClose();
    }

    if (!rename($tempFilePath, $newFilePath . '.tmp')) {
        array_push($errorArray, 'err_cannotrenamefile');
        logEntry('Unable to move the file ' . $tempFilePath, 'E_ERROR');
        returnErrorAndClose();
    } else {
        logEntry('File renamed ' . $newFilePath . '.tmp');
    }
}

function addDatabaseRecords($data, $fileuid)
{
    // TODO: must error check here if emails do not send or fails with data insertion.
    global $config, $functions;

    $data['fileuid'] = $fileuid;
    $data['filestatus'] = 'Available';
    $data['fileexpirydate'] = date($config['db_dateformat'], strtotime($data['fileexpirydate']));

    $emailTo = str_replace(',', ';', $data['fileto']);
    $emailArray = preg_split('/;/', $emailTo);

    // Loop though multiple emails and insert one DB record for each.
    foreach ($emailArray as $email) {
        $data['fileto'] = $email;
        $data['filevoucheruid'] = getGUID();

        if (isset($data['rtnemail'])) {
            // Whether or not to send an email copy to the sender.
            $data['rtnemail'] = $_REQUEST['rtnemail'];
        }

        logEntry('DEBUG fs_multi_upload: Filedata = ' . print_r($data, true));
        $functions->insertFile($data);
    }
}

function ensureFileSizesMatch($data, $uploadFolder, $tempFilename)
{
    global $errorArray;

    $tempFilePath = $uploadFolder . $tempFilename;

    if ($data['filesize'] != checkFileSize($tempFilePath)) {
        logEntry('DEBUG fs_multi_upload: File size incorrect after upload = Original:' . $data['filesize'] . ' != Actual:' . checkFileSize($tempFilePath) . ' - ' . $tempFilename);

        if (file_exists($tempFilePath)) {
            // Remove the offending file to prevent it trying to resume upload.
            unlink($tempFilePath);
            logEntry('DEBUG fs_multi_upload: File  ' . $tempFilename . ' was removed to prevent resume', 'E_ERROR');
        }

        array_push($errorArray, 'err_filesizeincorrect');
        returnErrorAndClose();
    }
}

function setAuthUserData($dataItem)
{
    global $authSaml, $authVoucher, $functions;

    if (!isset($dataItem['fileuid'])) {
        $dataItem['fileuid'] = getGUID();
    }

    if ($authVoucher->aVoucher()) {
        $tempData = $functions->getVoucherData($_REQUEST['vid']);
        $dataItem['fileauthuseruid'] = $tempData['fileauthuseruid'];
        $dataItem['fileauthuseremail'] = $tempData['filefrom'];
        $dataItem['fileuid'] = $_REQUEST['vid'];
    } elseif ($authSaml->isAuth()) {
        $authAttributes = $authSaml->sAuth();
        $dataItem['fileauthuseruid'] = $authAttributes['saml_uid_attribute'];
        $dataItem['fileauthuseremail'] = $dataItem['filefrom'];
        $dataItem['fileuid'] = $authAttributes['saml_uid_attribute'];
    }

    return $dataItem;
}


function insertPendingDbRecord($dataItem, $tempFilename)
{
    global $functions;

    $pendingData = $functions->checkPending($dataItem);

    if ($pendingData != '' && $tempFilename == generateTempFilename($pendingData, $_REQUEST['n'])) {
        $dataItem['filevoucheruid'] = $pendingData['filevoucheruid'];
        $functions->updateFile($dataItem);
    } else {
        $dataItem['filevoucheruid'] = getGUID();
        $dataItem['filestatus'] = 'Pending';
        $functions->insertFile($dataItem);
    }

    return $dataItem;
}

function isInvalidExpiryRange($dataItem)
{
    global $config;
    return strtotime($dataItem['fileexpirydate']) > strtotime('+' . $config['default_daysvalid'] . ' day') || strtotime($dataItem['fileexpirydate']) < strtotime('now');
}

function validateFields($dataItem)
{
    global $errorArray, $functions;

    if (!isset($dataItem['fileexpirydate'])) {
        // Expiry date is missing.
        array_push($errorArray, 'err_expmissing');
    }

    if (isInvalidExpiryRange($dataItem)) {
        // Don't generate a validation error but fix the expiry date to correct timezone/clock skew mishaps.
        $dataItem['fileexpirydate'] = $functions->ensureValidFileExpiryDate($dataItem['fileexpirydate']);
    }

    if (!isset($dataItem['fileto'])) {
        // To address is missing.
        array_push($errorArray, 'err_tomissing');
    } else {
        validateEmailAddresses($dataItem);
    }
}

function validateEmailAddresses($dataItem)
{
    global $config, $errorArray;

    // Separate email addresses.
    $emailTo = str_replace(',', ';', $dataItem['fileto']);
    $emailArray = preg_split('/;/', $emailTo);

    // Validate number of addresses.
    if (count($emailArray) > $config['max_email_recipients']) {
        array_push($errorArray, 'err_toomanyemail');
    }
    // Validate individual addresses.
    foreach ($emailArray as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errorArray, 'err_invalidemail');
        }
    }
}
