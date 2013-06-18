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
// this file is called by CRON to process incoming email bounces
// bounces are assumed to be stored in separate files per bounce
// and should contain the original X-FileSenderUID: header somewhere in
// the message. If not bounces are moved to the '../failures' directory
// for manual inspection.
// ---------------------------------

// required as this page is called from CRON not from a web browser
chdir(dirname(__FILE__));

// force all error reporting
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL);
}

$filesenderbase = dirname(dirname(__FILE__));

// include all required classes
require_once("$filesenderbase/config/config.php");

$CFG = config::getInstance();
$config = $CFG->loadConfig();

require_once("$filesenderbase/includes/ErrorHandler.php");
require_once("$filesenderbase/includes/UTF8.php");
require_once("$filesenderbase/classes/Functions.php");
require_once("$filesenderbase/classes/AuthSaml.php");
require_once("$filesenderbase/classes/AuthVoucher.php");
require_once("$filesenderbase/classes/DB.php");
require_once("$filesenderbase/classes/Mail.php");
require_once("$filesenderbase/classes/DB_Input_Checks.php");
require_once("$filesenderbase/classes/Log.php");

$sendmail = Mail::getInstance();
$functions = Functions::getInstance();

// set time zone for this session
date_default_timezone_set($config['Default_TimeZone']);

if (is_dir($config['emailbounce_location'])) {
	if ($dh = opendir($config['emailbounce_location'])) {
		while (($file = readdir($dh)) !== false) {
			if (preg_match("/^[.]*$/", $file)) { continue; }

			$fh = fopen($config['emailbounce_location'].'/'.$file, 'r');
			if (! $fh ) {
				echo "Can't open " . $config['emailbounce_location'] . "/" . $file . " for reading: exiting\n";
				continue;
			}

			$validmessage = 0;
			while ($line = fgets($fh)) {
				if (preg_match("/X-FileSenderUID:\s([\w]{8}-[\w]{4}-[\w]{4}-[\w]{4}-[\w]{12})/", $line, $vidstring)) {
					$dataitem['filevoucheruid'] = $vidstring[1];
					$voucherinfo = json_decode($functions->getFile($dataitem),true);

					$sender = $voucherinfo[0]['fileto'];
					$date = $voucherinfo[0]['filecreateddate'];
					$intendedrecipient = $voucherinfo[0]['fileto'];

					$dataitem["fileto"] = $voucherinfo[0]['filefrom'];
					$dataitem["fileoriginalto"] = $voucherinfo[0]['fileto'];
					$dataitem["filefrom"] = $config['return_path'];
					$dataitem["fileoriginalname"] = $voucherinfo[0]['fileoriginalname'];
					$dataitem["filename"] = $dataitem["fileoriginalname"];
					$dataitem["fileexpirydate"] = $voucherinfo[0]['fileexpirydate'];
					$dataitem["filemessage"] = $voucherinfo[0]['filemessage'];
					$dataitem["filesize"] = $voucherinfo[0]['filesize'];

					$sendmail->sendEmail($dataitem,$config['bouncenotification'],'bounce');
					fclose($fh);
					rename($config['emailbounce_location'].'/'.$file, $config['emailbounce_location'].'/../done/'.$file);
					// unlink($config['emailbounce_location'].'/'.$file);
					$validmessage = 1;
					break;
				} 
			}
			
			if ($validmessage == 0 ) {
				fclose($fh);
				rename($config['emailbounce_location'].'/'.$file, $config['emailbounce_location'].'/../failures/'.$file);
			}
		}
	}
}

closedir($dh);

?>
