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

class config {

private static $instance = NULL;

	public static function getInstance() {
		// Check for both equality and type
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

public function loadConfig() {

	$config = array();

	// Start of configurable settings
	// For more information about these settings please see the
	// Administrator Reference Manual in the documentation section
	// at www.filesender.org

	// General settings
	$config['admin'] = ''; // UID's (from $config['saml_uid_attribute']) that have Administrator permissions
	$config['adminEmail'] = ''; // Email address(es, separated by ,) to receive administrative messages (low disk space warning)
	$config['Default_TimeZone'] = 'Australia/Sydney';
	$config['site_defaultlanguage'] = 'en_AU'; // for available languages see the ./language directory
	$config['site_name'] = 'FileSender'; // Friendly name used for your FileSender instance
	$config['noreply'] = 'noreply@filesender.org'; // default no-reply email address 

    $config["customCSS"] = "";

	// UI Settings
	$config['datedisplayformat'] = "d-m-Y"; // Format for displaying date/time, use PHP date() format string syntax
	$config["versionNumber"] = true; // Show version number (true/false)
	$config['site_showStats'] = false; // Show site upload/download stats (true/false)
    $config['upload_box_default_size'] = 3; // Default number of files that fit in the upload box before a scroll-bar appears
    
	// auto complete - provides auto complete in input field for emails
	$config["autocomplete"] = true;
	$config["autocompleteHistoryMax"] = ""; // "" - unlimited or integer, number of results displayed in autocomplete

	// debug settings
	$config["debug"] = false; // Debug logging on/off (true/false)
	$config["displayerrors"] = false; // Display debug errors on screen (true/false)
	$config['dnslookup'] = true; // log includes DNS lookup (true/false)
	$config["client_specific_logging"] = false; // client logging (true/false)
	$config["client_specific_logging_uids"] = ""; // "" is log all clients, or log for specific userid's or voucheruid's seperated by comma 'xxxx,zzzzz'

	// saml settings
	$config['saml_email_attribute'] = 'mail'; // Attribute used for email address
	$config['saml_name_attribute'] = 'cn'; // Attribute used to get the user's name
	$config['saml_uid_attribute'] = 'eduPersonTargetedID'; // Attribute to uniquely identify the user

	// AuP settings
	$config["AuP_default"] = false; //AuP value is already ticked
	$config["AuP"] = true; // AuP is displayed

    // Mac OS X alternative unzip application (for multiple file downloads where the default Archive Utility will not work).
    $config['mac_unzip_name'] = 'The Unarchiver';
    $config['mac_unzip_link'] = 'http://unarchiver.c3.cx/unarchiver';

	// Server settings
	$config['default_daysvalid'] = 20; // Maximum number of days before file/voucher is expired
	$config['ban_extension'] = 'exe,bat'; // Possibly dangerous file extensions that are disallowed
	$config["max_email_recipients"] = 100; // maximum email addresses allowed to send at once for voucher or file sending, a value of 0 allows unlimited emails.

	$config['max_flash_upload_size'] = '2147483648'; // 2GB
	$config['max_html5_upload_size'] = '107374182400'; // 100  GB
	$config["upload_chunk_size"]  = '2000000';//
    $config["download_chunk_size"] = '5242880'; // The maximum amount of data that will be read into memory at once during multi-file downloads, default 5MB.
    $config["html5_max_uploads"] = 30; // Max number of simultaneous uploads.

	// update max_flash_upload_size if php.ini post_max_size and upload_max_filesize is set lower
	$config['max_flash_upload_size'] = min(let_to_num(ini_get('post_max_size'))-2048, let_to_num(ini_get('upload_max_filesize')),$config['max_flash_upload_size']);

	$config["server_drivespace_warning"] = 20; // as a percentage 20 = 20% space left on the storage drive

	// Terasender (fast upload) settings
	// - terasender (really fast uploads) uses html5 web workers to speed up file upload
	// - effectively providing multi-threaded faster uploads
	$config['terasender'] = true; // true/false
	$config['terasenderadvanced'] = false; // true/false - terasender advanced - show advanced settings
	$config['terasender_chunksize'] = 5;		// default (5) terasender chunk size in MB
	$config['terasender_workerCount'] = 6;		// default (6) worker count
	$config['terasender_jobsPerWorker'] = 1;	// default (1) jobs per worker

    // Email flow settings
    // Can be either 'always' 'hidden' or 'off'
    // settings marked as 'always' will be displayed in the right hand column of the upload page
    // settings marked as 'hidden' will be contained in 'More options' in the right hand column of the upload page
    // settings marked as 'off' are completely disabled and are not displayed anywhere
    // true/false on default fields specify whether or not boxes are checked on page load
    $config['email_me_copies_display'] = 'off';
    $config['email_me_copies_default'] = false;

    $config['upload_complete_email_display'] = 'always';
    $config['upload_complete_email_default'] = true;

    $config['inform_download_email_display'] = 'hidden';
    $config['inform_download_email_default'] = true;

    $config['email_me_daily_statistics_display'] = 'always';
    $config['email_me_daily_statistics_default'] = false;

    $config['download_confirmation_enabled_display'] = 'hidden';
    $config['download_confirmation_enabled_default'] = true;

    $config['add_me_to_recipients_display'] = 'hidden';
    $config['add_me_to_recipients_default'] = false;

	// Advanced server settings, do not change unless you have a very good reason.
	$config['db_dateformat'] = "Y-m-d H:i:sP"; // Date/Time format for PostgreSQL, use PHP date format specifier syntax
	$config["crlf"] = "\n"; // for email CRLF can be changed to \r\n if required
	$config['voucherRegEx'] = "'[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}'";
	$config['voucherUIDLength'] = 36;
    $config['openSSLKeyLength'] = 30;
	$config['emailRegEx'] = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
    $config['webWorkersLimit'] = 16; // The highest number of web workers that is supported by all modern browsers (currently constrained by Opera).

	// site URL settings
	if ( isset($_SERVER['SERVER_NAME']) ) {
	$prot =  isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$config['site_url'] = $prot . $_SERVER['SERVER_NAME'] . '/filesender/'; // URL to Filesender
	$config['site_simplesamlurl'] =  $prot . $_SERVER['SERVER_NAME'] . '/simplesaml/';
	$config['site_authenticationSource'] ="default-sp";
	$config['site_logouturl'] = $config['site_url'] . '?s=logout';
	}
	$config['forceSSL'] = true; // Always use SSL (true/false)

	// Support links
	$config['aboutURL'] = "";
	$config['helpURL'] = "";

	// (absolute) file locations
	$config['site_filestore'] = '/usr/share/filesender/files/';
	$config['site_temp_filestore'] = '/usr/share/filesender/tmp/';
	$config['site_simplesamllocation'] = '/usr/share/simplesamlphp/';
	$config['log_location'] = '/usr/share/filesender/log/';

	$config["db_type"] = "pgsql";// pgsql or mysql
	$config['db_host'] = 'localhost';
	$config['db_database'] = 'filesender';
	$config['db_port'] = '5432';
	// database username and password
	$config['db_username'] = 'filesender';
	$config['db_password'] = 'yoursecretpassword';

	//Optional DSN format overides db_ settings
	//$config['dsn'] = "pgsql:host=localhost;dbname=filesender";
	//$config['dsn'] = 'pgsql:host=localhost;dbname=filesender';
	//$config['dsn'] = 'sqlite:/usr/share/filesender/db/filesender.sqlite';
	//$config['dsn_driver_options'] = array();
	// dsn requires username and password in $config['db_username'] and $config['db_password']

	// cron settings
	$config['cron_exclude prefix'] = '_'; // exclude deletion of files with the prefix character listed (can use multiple characters eg '._' will ignore .xxxx and _xxxx
	$config['cron_shred'] = false; // instead of simply unlinking, overwrite expired files so they are hard to recover
	$config['cron_shred_command'] = '/usr/bin/shred -f -u -n 1 -z'; // overwrite once (-n 1) with random data, once with zeros (-z), then remove (-u)
	$config["cron_cleanuptempdays"] = 7; // number of days to keep temporary files in the temp_filestore

	// End of configurable settings

	return $config;
	}
}

// Helper function used when calculating maximum upload size from the various maxsize configuration items
function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $ret = trim($v);
    $last = strtoupper($ret[strlen($ret)-1]);
    switch($last) {
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
      return $ret;
}
?>
