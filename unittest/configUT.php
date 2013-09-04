<?php
// Tests for Filsender Version 1.6
require_once('../simpletest/autorun.php');
require_once('../config/config.php');

class testFileSender_configUT extends UnitTestCase {
	 
	function test_configUT()  
    {
		$CFG = new Config();
	 $config = $CFG->loadConfig();
	// check all required options exist in config file
	$this->assertTrue(isset($config["customCSS"]));
	$this->assertTrue(isset($config["admin"]));
	$this->assertTrue(isset($config["adminEmail"]));
	$this->assertTrue(isset($config["Default_TimeZone"]));
	$this->assertTrue(isset($config["site_name"]));
    $this->assertTrue(isset($config["customCSS"]));

	$this->assertTrue(isset($config["terasender"])); // true/false - terasender (really fast uploads)uses html5 web workers to speed up file upload - effectively provding multi thread faster uploads
	$this->assertTrue(isset($config["terasenderadvanced"])); // true/false - terasender advanced - show advanced settings
	$this->assertTrue(isset($config["terasender_chunksize"]));
	$this->assertTrue(isset($config["terasender_workerCount"]));
	$this->assertTrue(isset($config["terasender_jobsPerWorker"]));


 // Email flow settings
    // Can be either 'always' 'hidden' or 'off'
    // settings marked as 'always' will be displayed in the right hand column of the upload page
    // settings marked as 'hidden' will be contained in 'More options' in the right hand column of the upload page
    // settings marked as 'off' are completely disabled and are not displayed anywhere
    // true/false on default fields specify whether or not boxes are checked on page load
   $this->assertTrue( isset($config["email_me_copies_display"]));
   $this->assertTrue( isset($config["email_me_copies_default"]));

   $this->assertTrue( isset($config["upload_complete_email_display"]));
   $this->assertTrue( isset($config["upload_complete_email_default"]));

    $this->assertTrue(isset($config["inform_download_email_display"]));
   $this->assertTrue( isset($config["inform_download_email_default"]));

    $this->assertTrue(isset($config["email_me_daily_statistics_display"]));
    $this->assertTrue(isset($config["email_me_daily_statistics_default"]));

   $this->assertTrue( isset($config["download_confirmation_enabled_display"]));
   $this->assertTrue( isset($config["download_confirmation_enabled_default"]));

   $this->assertTrue( isset($config["email_only_me_display"]));
   $this->assertTrue( isset($config["email_only_me_default"]));

	$this->assertTrue(isset($config["openSSLKeyLength"]));
	$this->assertTrue(isset($config["emailRegEx"]));
   $this->assertTrue( isset($config["webWorkersLimit"]));

	// UI Settings
	$this->assertTrue(isset($config["datedisplayformat"]));
	$this->assertTrue(isset($config["versionNumber"])); // Show version number (true/false)
	$this->assertTrue(isset($config["site_showStats"])); // Show site upload/download stats (true/false)
	$this->assertTrue(isset($config["displayUserName"])); // Show 'Welcome user' (true/false)
	
	// multi file option
	$this->assertTrue(isset($config["multiupload"]));
    
    // auto complete - provides auto complete in input field for emails
    $this->assertTrue(isset($config["autocomplete"]));
   $this->assertTrue( isset($config["autocompleteHistoryMax"]));

 // Mac OS X alternative unzip application (for multiple file downloads where the default Archive Utility will not work).
   $this->assertTrue( isset($config["mac_unzip_name"]));
    $this->assertTrue(isset($config["mac_unzip_link"]));
	
	// advanced options
	// voucherreturnlock - vouchers can only be used to upload files back to the voucher creator
	$this->assertTrue(isset($config["voucherreturnlock"]));
	$this->assertTrue(isset($config["displaydownloadsummary"]));
	
	// options - stored as array in fileoptions 
	// Voucher Locked To Sender
	$this->assertTrue(isset($config["vlts"]));

	// debug settings
	$this->assertTrue(isset($config["debug"]));
	$this->assertTrue(isset($config["dnslookup"]));
	$this->assertTrue(isset($config["client_specific_logging"]));
	$this->assertTrue(isset($config["client_specific_logging_uids"]));

	// saml settings
	$this->assertTrue(isset($config["saml_email_attribute"]));
	$this->assertTrue(isset($config["saml_name_attribute"]));
	$this->assertTrue(isset($config["saml_uid_attribute"]));

	// AuP settings
	$this->assertTrue(isset($config["AuP_default"]));
	$this->assertTrue(isset($config["AuP"]));
	//isset($config["AuP_label"] = "I accept the terms and conditions of this service"; // moved AUP to language files
	//isset($config["AuP_terms"] = "AuP Terms and conditions"; // moved AUP to language files

	// Server settings
	$this->assertTrue(isset($config["default_daysvalid"]));
	$this->assertTrue(isset($config["ban_extension"]));
	$this->assertTrue(isset($config["max_email_recipients"]));

	$this->assertTrue(isset($config["max_flash_upload_size"]));
	$this->assertTrue(isset($config["max_gears_upload_size"]));
	$this->assertTrue(isset($config["max_html5_upload_size"]));
	$this->assertTrue(isset($config["upload_chunk_size"]));
	$this->assertTrue(isset($config["download_chunk_size"]));
    $this->assertTrue(isset($config["html5_max_uploads"]));
	
	// update max_flash_upload_size if php.ini post_max_size and upload_max_filesize is set lower
	$this->assertTrue(isset($config["max_flash_upload_size"]));
	
	$this->assertTrue(isset($config["server_drivespace_warning"]));
	
	// Advanced server settings, do not change unless you have a very good reason.
	$this->assertTrue(isset($config["postgresdateformat"]));
	$this->assertTrue(isset($config["db_dateformat"]));
	$this->assertTrue(isset($config["crlf"]));
	$this->assertTrue(isset($config["voucherRegEx"]));
	$this->assertTrue(isset($config["voucherUIDLength"]));
	$this->assertTrue(isset($config["emailRegEx"]));

	// site URL settings
	$this->assertTrue(isset($config["site_url"]));
	$this->assertTrue(isset($config["site_simplesamlurl"]));
	$this->assertTrue(isset($config["site_authenticationSource"]));
	$this->assertTrue(isset($config["site_logouturl"])); 
	$this->assertTrue(isset($config["site_downloadurl"]));
	
	$this->assertTrue(isset($config["forceSSL"]));
	$this->assertTrue(isset($config["displayerrors"]));
	// Support links
	$this->assertTrue(isset($config["aboutURL"]));
	$this->assertTrue(isset($config["helpURL"]));
	$this->assertTrue(isset($config["HTML5URL"]));
	//config['gearsURL'] = 'http://tools.google.com/gears/';

	// (absolute) file locations
	$this->assertTrue(isset($config["site_filestore"]));
	$this->assertTrue(isset($config["site_temp_filestore"])); 
	$this->assertTrue(isset($config["site_simplesamllocation"]));
	$this->assertTrue(isset($config["log_location"]));	
	
	$this->assertTrue(isset($config["db_type"]));
	$this->assertTrue(isset($config["db_host"]));
	$this->assertTrue(isset($config["db_database"]));
	$this->assertTrue(isset($config["db_port"]));
	// database username and password
	$this->assertTrue(isset($config["db_username"]));
	$this->assertTrue(isset($config["db_password"]));
	
	// cron settings
	$this->assertTrue(isset($config["cron_exclude prefix"]));
	$this->assertTrue(isset($config["cron_shred"]));
	$this->assertTrue(isset($config["cron_shred_command"]));
	$this->assertTrue(isset($config["cron_cleanuptempdays"]));
	
	// email templates section
	$this->assertTrue(isset($config["default_emailsubject"]));
	}
	
}

?>