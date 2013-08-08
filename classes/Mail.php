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
 
 
//  --------------------------------
// email class
// ---------------------------------
class Mail {

    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ---------------------------------------
    // Send an upload confirmation email to the file sender ("filefrom" field).
    // $fileDetails should be an array returned from getMultiFileData() or getTransactionDetails().
    // ---------------------------------------
    public function sendUploadConfirmation($fileDetails) {
        global $config;

        $fileDetails[0]['fileto'] = $fileDetails[0]['filefrom'];
        return $this->sendEmail($fileDetails[0], lang('_EMAIL_BODY_FILES_UPLOADED'), 'full', $fileDetails);
    }

    // ---------------------------------------
    // Send a "Download is available" email to recipient(s).
    // $groupIds can be either an array of IDs (for multiple recipients) or a string (single recipient).
    // ---------------------------------------
    public function sendDownloadAvailable($groupIds) {
        $functions = Functions::getInstance();

        if (is_string($groupIds)) {
            // Convert to array to enable looping.
            $groupIds = array($groupIds);
        }

        if (!is_array($groupIds)) {
            logEntry('Mail.php: Invalid parameter $groupIds - must be string or array', 'E_ERROR');
            return false;
        }

        foreach($groupIds as $groupId) {
            // Send email(s) if group ID is valid.
            if (!ensureSaneOpenSSLKey($groupId)) {
                logEntry('Mail.php: Invalid group ID ' . $groupId, 'E_ERROR');
                return false;
            }

            $emailData = $functions->getMultiFileData($groupId);

            if (empty($emailData)) {
                logEntry('Mail.php: No file data was found for group ID ' . $groupId, 'E_ERROR');
                return false;
            }

            if (!$this->sendEmail($emailData[0], lang('_EMAIL_BODY_DOWNLOAD_AVAILABLE'), 'full', $emailData)) {
                // Sending failed, no need to log as sendEmail() does that.
                return false;
            }
        }

        return true; // Email(s) sent successfully.
    }

    // ---------------------------------------
    // Send a "file has been downloaded" email to sender.
    // $voucherIds can be either an array of IDs (for multiple files) or a string (single file).
    // ---------------------------------------
    public function sendDownloadNotification($voucherIds) {
        $functions = Functions::getInstance();

        if (is_string($voucherIds)) {
            // Convert to array to enable looping.
            $voucherIds = array($voucherIds);
        }

        if (!is_array($voucherIds)) {
            logEntry('Mail.php: Invalid parameter $voucherIds - must be string or array', 'E_ERROR');
            return false;
        }

        $files = array();
        foreach ($voucherIds as $voucherId) {
            if (!ensureSaneFileUid($voucherId)) {
                logEntry('Mail.php: Invalid voucher ID ' . $voucherId, 'E_ERROR');
                return false;
            }

            $voucherData = $functions->getVoucherData($voucherId);

            if (empty($voucherData)) {
                logEntry('Mail.php: No file data was found for voucher ID ' . $voucherData, 'E_ERROR');
                return false;
            }

            $files[] = $voucherData;
        }

        $temp = $files[0]['fileto'];
        $files[0]['fileto'] = $files[0]['filefrom'];
        $files[0]['filefrom'] = $temp;

        if (!$this->sendEmail($files[0], lang('_EMAIL_BODY_FILES_DOWNLOADED'), 'full', $files)) {
            // Sending failed, no need to log as sendEmail() does that.
            return false;
        }

        return true;
    }

    // ---------------------------------------
    // Send a notification that a transaction has been deleted.
    // $recipient should be an array from getMultiFileData().
    // $notifyRecipient is a boolean indicating whether to notify the recipients (and not just the sender).
    // ---------------------------------------
    public function sendRecipientDeleted($recipient, $notifyRecipient) {
        $recipient[0]['recemail'] = $recipient[0]['fileto'];
        $recipient[0]['fileto'] = $recipient[0]['filefrom'];

        if (!$this->sendEmail($recipient[0], lang('_EMAIL_BODY_RECIPIENT_DELETED'))) {
            return false;
        }

        if ($notifyRecipient) {
            $recipient[0]['fileto'] = $recipient[0]['recemail'];

            if (!$this->sendEmail($recipient[0], lang('_EMAIL_BODY_TRANSACTION_NO_LONGER_AVAILABLE'))) {
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------
    // Send a notification that a transaction has been deleted.
    // $recipients should be an array from getMultiRecipientDetails().
    // $notifyRecipients is a boolean indicating whether to notify the recipients (and not just the sender).
    // ---------------------------------------
    public function sendTransactionDeleted($recipients, $notifyRecipients) {
        $temp = $recipients[0]['fileto'];
        $recipients[0]['fileto'] = $recipients[0]['filefrom'];

        if (!$this->sendEmail($recipients[0], lang('_EMAIL_BODY_TRANSACTION_DELETED'))) {
            return false;
        }

        if ($notifyRecipients) {
            $recipients[0]['fileto'] = $temp;
            foreach ($recipients as $recipient) {
                if (!$this->sendEmail($recipient, lang('_EMAIL_BODY_TRANSACTION_NO_LONGER_AVAILABLE'))) {
                    return false;
                }
            }
        }

        return true;
    }

    public function sendVoucherIssued($voucherId) {
        global $functions;

        $data = $functions->getVoucherData($voucherId);

        // Email a receipt to the uploader.
        $data['recemail'] = $data['fileto'];
        $data['fileto'] = $data['filefrom'];

        if (!$this->sendEmail($data, lang('_EMAIL_BODY_VOUCHER_ISSUED_RECEIPT'))) {
            return false;
        }

        // Send email to recipient.
        $data['fileto'] = $data['recemail'];

        if (!$this->sendEmail($data, lang('_EMAIL_BODY_VOUCHER_ISSUED'))) {
            return false;
        }

        return true;
    }

    public function sendVoucherCancelled($voucherId) {
        global $functions;

        $data = $functions->getVoucherData($voucherId);

        // Email a receipt to the uploader.
        $data['recemail'] = $data['fileto'];
        $data['fileto'] = $data['filefrom'];

        if (!$this->sendEmail($data, lang('_EMAIL_BODY_VOUCHER_CANCELLED_RECEIPT'))) {
            return false;
        }

        // Send email to recipient.
        $data['fileto'] = $data['recemail'];

        if (!$this->sendEmail($data, lang('_EMAIL_BODY_VOUCHER_CANCELLED'))) {
            return false;
        }

        return true;
    }

    // ---------------------------------------
    // Send a daily transaction summary email. Uses information from the logs table, so does not use sendEmail().
    // $transactionDetails is an array of transactions (from logs) by a given user within the last 24 hours.
    // ---------------------------------------
    public function sendSummary($transactionDetails) {
        global $config;

        if (!filter_var($transactionDetails[0]['logto'], FILTER_VALIDATE_EMAIL)) {
            logEntry('Invalid email address ' . $transactionDetails[0]['logto']);
            return false;
        }

        // Replace template variables.
        $to = $transactionDetails[0]['logfrom'];

        $headers = "MIME-Version: 1.0" . $config['crlf'];
        $headers .= "Content-Type: multipart/alternative; boundary=simple_mime_boundary" . $config['crlf'];
        $headers .= "From: " . $config['noreply'] . $config['crlf'];

        $subject = lang('_EMAIL_SUBJECT_SUMMARY');
        $subject = str_replace('{siteName}',  $config['site_name'], $subject);
        $subject = str_replace('{filetrackingcode}', $transactionDetails[0]['logfiletrackingcode'], $subject);

        $message = lang('_EMAIL_BODY_SUMMARY');
        $message = str_replace('{siteName}',  $config['site_name'], $message);
        $message = str_replace('{filetrackingcode}', $transactionDetails[0]['logfiletrackingcode'], $message);
        $message = str_replace('{CRLF}', $config['crlf'], $message);
        $message = $this->createEmailBody($message);

        $activities = array();

        foreach ($transactionDetails as $transaction) {
            // Truncate the seconds from the logdate field in order to join together events that happened in the same minute.
            $transaction['logdate'] = floor(strtotime($transaction['logdate']) / 60) * 60;
            $found = false;

            // Count the number of files that the user downloaded from this transaction within the same minute.
            foreach ($activities as &$activity) {
                if ($transaction['logtype'] == $activity['logtype'] && $transaction['logdate'] == $activity['logdate'] && $transaction['logto'] == $activity['logto']) {
                    $activity['count'] += 1;
                    $found = true;
                }
            }

            unset($activity); // Need unset() because of pass-by-reference in the above loop.

            if (!$found) {
                // First download by this user, set count to 1.
                $transaction['count'] = 1;
                $activities[] = $transaction;
            }
        }

        $activityTemplate = '';
        $htmlActivityTemplate = '';

        foreach ($activities as $activity) {
            // Build the activity string that is output to the email.
            $fileCount = $activity['count'] == 1 ? ' file' : ' files';

            switch ($activity['logtype']) {
                case 'Download':
                    // E.g. "16:38: recipient@email.com downloaded 3 files".
                    $str = $activity['logto'] . ' downloaded ' . $activity['count'] . $fileCount;
                    break;
                case 'Added':
                    // E.g. "16:35: recipient2@email.com was added to the transaction".
                    $str = $activity['logto'] . ' was added to the transaction';
                    break;
                case 'Uploaded':
                    // E.g. "16:30: uploader@email.com sent 3 files to recipient@email.com".
                    $str = $activity['logfrom'] . ' sent ' . $activity['count'] . $fileCount . ' to ' . $activity['logto'];
                    break;
                case 'Removed':
                    // E.g. "16:45: recipient2@email.com was removed from the transaction".
                    $str = $activity['logto'] . ' was removed from the transaction';
                    break;
            }

            if (isset($str)) {
                // Add the timestamp, bullet points and line shifts.
                $str = date("H:i", $activity['logdate']) . ': ' . $str;
                $activityTemplate .= ' - ' . $str . $config['crlf'];
                $htmlActivityTemplate .= '&nbsp;&bull;&nbsp;' . $str . '<br />';
            }

            unset($str);
        }

        $message = str_replace('{transactionactivity}', $activityTemplate, $message);
        $message = str_replace('{htmltransactionactivity}', $htmlActivityTemplate, $message);

        try {
            if (mail($to, $subject, $message, $headers)) {
                return true;
            } else {
                logEntry('Error sending email: ' . $to, 'E_ERROR');
                return false;
            }
        } catch (Exception $e) {
            logEntry($e->getMessage(), 'E_ERROR');
            return false;
        }
    }

    //---------------------------------------
    // Send mail
    // 
    public function sendEmail($mailObject, $template, $type = 'full', $multiFileDetails = null){
        global $config;
		global $errorArray;

        if (!filter_var($mailObject['filefrom'], FILTER_VALIDATE_EMAIL) || !filter_var($mailObject['fileto'], FILTER_VALIDATE_EMAIL)) {
            // Invalid email address(es).
            return false;
        }

        if ($multiFileDetails == null) {
            $template = $this->replaceTemplateVariables($template, $mailObject);
        } else {
            $template = $this->replaceMultiFileTemplateVariables($template, $multiFileDetails);
        }
		
		// Need to use $config['noreply'] so that sender does not get bombarded with emails
		if (isset($mailObject['rtnemail']) && $mailObject['rtnemail'] == 'false' && isset($config['noreply'])) {
		    $mailObject['filefrom'] = $config['noreply'];
		}

        $to = '<' . $mailObject['fileto'] . '>';
        $returnPath = $this->createEmailReturnPath($mailObject, $type);
        $headers = $this->createEmailHeaders($mailObject);
        $subject = $this->createEmailSubject($mailObject);
        $body = $this->createEmailBody($template);

		try {
		    if (mail($to, $subject, $body, $headers, $returnPath)) {
		 	    return true;
		    } else {
		 	    logEntry('Error sending email: ' . $to, 'E_ERROR');
			    array_push($errorArray, 'err_emailnotsent');
			    return false;
		    }
        } catch(Exception $e) {
            logEntry($e->getMessage(), 'E_ERROR');
			array_push($errorArray,  'err_emailnotsent');
		    return false;
        }
    }

    private function replaceTemplateVariables($template, $mailObject, $type = 'full') {
        global $config;

        $fileoriginalname = sanitizeFilename($mailObject['fileoriginalname']);
        $crlf = $config['crlf'];

        if (isset($config['site_url'])) {
            $template = str_replace('{serverURL}', $config['site_url'], $template);
        }

        if (isset($mailObject['recemail'])) {
            $template = str_replace('{recemail}', $mailObject['recemail'], $template);
        }

        $template = str_replace('{siteName}', $config['site_name'], $template);
        $template = str_replace('{fileto}', $mailObject['fileto'], $template);
        $template = str_replace('{filevoucheruid}', $mailObject['filevoucheruid'], $template);
        $template = str_replace('{filegroupid}', $mailObject['filegroupid'], $template);
        $template = str_replace('{filetrackingcode}', $mailObject['filetrackingcode'], $template);
        $template = str_replace('{fileexpirydate}', date($config['datedisplayformat'], strtotime($mailObject['fileexpirydate'])), $template);
        $template = str_replace('{filefrom}', $mailObject['filefrom'], $template);
        $template = str_replace('{fileoriginalname}', $fileoriginalname, $template);
        $template = str_replace('{htmlfileoriginalname}', utf8tohtml($fileoriginalname, TRUE), $template);
        $template = str_replace('{filename}', $fileoriginalname, $template);
        $template = str_replace('{filesize}', formatBytes($mailObject['filesize']), $template);
        $template = str_replace('{CRLF}', $crlf, $template);

        if ($type == 'bounce') {
            $template = str_replace('{fileoriginalto}', $mailObject['fileoriginalto'], $template);
        }

        if(strlen($mailObject['filemessage']) > 0) {
            // Remove {filemessage_start} and {filemessage_end} tags, and keep what's in there.
            $template = preg_replace('/{filemessage_start}(.*?){filemessage_end}/sm', '$1', $template);

            // Replace 'newlines' (various formats) in filemessage with $crlf and count the number of lines.
            $mailObject['filemessage'] = preg_replace("/\r\n|\n|\r/", $crlf , $mailObject["filemessage"], -1, $nlcount);

            // Encode the 'filemessage' with a UTF8-safe version of htmlentities to allow for multibyte UTF-8 characters.
            // Also insert <br /> linebreak tags to preserve intended formatting in the HTML body part.
            $mailObject['htmlfilemessage'] = nl2br(utf8tohtml($mailObject['filemessage'], TRUE));

            // Add extra newlines when filemessage contains more than a few words
            // (to get a better layout in the non HTML body part)
            if ($nlcount > 0) {
                $mailObject['filemessage'] = $crlf . $crlf . $mailObject['filemessage'];
            }

            $template = str_replace('{filemessage}', $mailObject['filemessage'], $template);
            $template = str_replace('{htmlfilemessage}', $mailObject['htmlfilemessage'], $template);
        } else {
            // No file message, remove {filemessage_start} and {filemessage_end} tags, as well as what's in there.
            $template = preg_replace('/{filemessage_start}(.*?){filemessage_end}/sm', '', $template);
        }

        return $template;
    }

    private function replaceMultiFileTemplateVariables($template, $transactionDetails) {
        global $config;

        $template = $this->replaceTemplateVariables($template, $transactionDetails[0]);
        $fileInfo = '';
        $htmlFileInfo = '';

        foreach ($transactionDetails as $file) {
            $fileString = $file['fileoriginalname'] . ' (' . formatBytes($file['filesize']) . ')';
            $fileInfo .= ' - ' . $fileString . $config['crlf'];
            $htmlFileInfo .= '&nbsp;&bull;&nbsp;' . $fileString . '<br />';
        }

        $template = str_replace('{fileinfo}', $fileInfo, $template);
        $template = str_replace('{htmlfileinfo}', $htmlFileInfo, $template);

        return $template;
    }

    private function createEmailHeaders($mailObject) {
        global $config;
        $crlf = $config['crlf'];

        $headers = 'MIME-Version: 1.0' . $crlf;
        $headers .= 'Content-Type: multipart/alternative; boundary=simple_mime_boundary' . $crlf;
        $headers .= 'X-FileSenderUID: ' . $mailObject['filevoucheruid'] . $crlf;
        $headers .= 'From: <' . $mailObject['filefrom'] . '>' . $crlf; // RFC2822 Originator of the message

        if (isset($mailObject['rtnemail']) && $mailObject['rtnemail'] == 'true') {
            $headers .= 'Cc: <' . $mailObject['filefrom'] . '>' . $crlf;
        }

        return $headers;
    }

    private function createEmailSubject($mailObject, $type = 'full') {
        global $config;

        if (isset($mailObject['filesubject']) && $mailObject['filesubject'] != "" && $type != 'bounce') {
            $subject = $config['site_name'] . ': ' . $mailObject['filesubject'];
        } else {
            if ($type == 'bounce') {
                $tempFileSubject = lang('_EMAIL_SUBJECT_BOUNCE');
            } else {
                $tempFileSubject = lang('_EMAIL_SUBJECT_DEFAULT');
            }

            $fileOriginalName = sanitizeFilename($mailObject['fileoriginalname']);
            $tempFileSubject = str_replace('{siteName}', $config['site_name'], $tempFileSubject);
            $tempFileSubject = str_replace('{fileoriginalname}', $fileOriginalName, $tempFileSubject);
            $tempFileSubject = str_replace('{filename}', $fileOriginalName, $tempFileSubject);
            $tempFileSubject = str_replace('{filetrackingcode}', $mailObject['filetrackingcode'], $tempFileSubject);

            $subject = $tempFileSubject;
        }

        // Check needed encoding for subject (assumes UTF-8).
        $encoding = detect_char_encoding($subject) ;

        if ($encoding != 'US-ASCII') {
            $subject = mime_qp_encode_header_value($subject, 'UTF-8', $encoding, $config['crlf']) ;
        }

        return $subject;
    }

    private function createEmailReturnPath($mailObject, $type = 'full') {
        // RFC2821 (Envelope) originator of the message
        global $config;
        $crlf = $config['crlf'];

        if ($type == 'bounce') {
            $returnPath = '-r <>' . $crlf;
        } else if (isset($config['return_path']) && !empty($config['return_path'])) {
            if (!filter_var($config['return_path'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $returnPath = '-r <' . $config['return_path'] . '>' . $crlf;
        } else {
            $returnPath = '-r <' . $mailObject['filefrom'] . '>' . $crlf;
        }

        return $returnPath;
    }

    private function createEmailBody($template) {
        // Check and set the needed encoding for the body, convert if necessary.
        require_once("../includes/UTF8.php");
        $body_encoding = detect_char_encoding($template) ;
        $template = str_replace('{charset}', $body_encoding, $template);

        if ($body_encoding == 'ISO-8859-1') {
            $template = iconv('UTF-8', 'ISO-8859-1', $template);
        }

        return wordwrap($template, 70);
    }

	//---------------------------------------
    // Send summary 
    // 	
	
	 /*public function sendSummary($to,$message){

        // sends a summary 

        global $config;

        $crlf = $config["crlf"];

        $headers = "MIME-Version: 1.0".$crlf;
        $headers .= "Content-Type: multipart/alternative; boundary=simple_mime_boundary".$crlf;
        //$headers .= "From: noreply@".$_SERVER['HTTP_HOST'].$crlf;
 		$headers .= "From: ".$to.$crlf;
        //$headers .= "Reply-To: ".$to.$crlf;
        //$returnpath = "-r".$mailobject['filefrom'].$crlf;

        if(!filter_var($to,FILTER_VALIDATE_EMAIL)) {return false;}

        $subject = $config['site_name']." - Summary for " .$to;
        $body = wordwrap($crlf ."--simple_mime_boundary".$crlf ."Content-type:text/plain; charset=iso-8859-1".$crlf.$crlf .$message,70);
		
        if (mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }*/
	
    //---------------------------------------
    // Send admin mail messages
    // 	
    public function sendEmailAdmin($message){

        // send admin notifications via email

        global $config;

        $crlf = $config["crlf"];

        $headers = "MIME-Version: 1.0".$crlf;
        $headers .= "Content-Type: multipart/alternative; boundary=simple_mime_boundary".$crlf;
        $headers .= "From: noreply@".$_SERVER['HTTP_HOST'].$crlf;

        //$headers .= "Reply-To: ".$mailobject['filefrom'].$crlf;
        //$returnpath = "-r".$mailobject['filefrom'].$crlf;

        $to = $config['adminEmail'];
        if(!filter_var($to,FILTER_VALIDATE_EMAIL)) {return false;}

        $subject =   $config['site_name']." - Admin Message";
        $body = wordwrap($crlf ."--simple_mime_boundary".$crlf ."Content-type:text/plain; charset=iso-8859-1".$crlf.$crlf .$message,70);

        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            return false;
        }
    }

}
?>
