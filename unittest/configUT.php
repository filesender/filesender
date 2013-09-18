<?php
// Tests for Filsender Version 1.6
require_once('../simpletest/autorun.php');
require_once('../config/config.php');

class testFileSender_configUT extends UnitTestCase {
	 
	function test_configUT()  
    {
	
	// check all required options exist in config file
	$this->assertTrue(set("customCSS"));
	$this->assertTrue(set("admin"));
	$this->assertTrue(set("adminEmail"));
	$this->assertTrue(set("Default_TimeZone"));
	$this->assertTrue(set("site_name"));
    $this->assertTrue(set("customCSS"));

	$this->assertTrue(set("terasender")); // true/false - terasender (really fast uploads)uses html5 web workers to speed up file upload - effectively provding multi thread faster uploads
	$this->assertTrue(set("terasenderadvanced")); // true/false - terasender advanced - show advanced settings
	$this->assertTrue(set("terasender_chunksize"));
	$this->assertTrue(set("terasender_workerCount"));
	$this->assertTrue(set("terasender_jobsPerWorker"));


 // Email flow settings
    // Can be either 'always' 'hidden' or 'off'
    // settings marked as 'always' will be displayed in the right hand column of the upload page
    // settings marked as 'hidden' will be contained in 'More options' in the right hand column of the upload page
    // settings marked as 'off' are completely disabled and are not displayed anywhere
    // true/false on default fields specify whether or not boxes are checked on page load
   $this->assertTrue( set("email_me_copies_display"));
   $this->assertTrue( set("email_me_copies_default"));

   $this->assertTrue( set("upload_complete_email_display"));
   $this->assertTrue( set("upload_complete_email_default"));

    $this->assertTrue(set("inform_download_email_display"));
   $this->assertTrue( set("inform_download_email_default"));

    $this->assertTrue(set("email_me_daily_statistics_display"));
    $this->assertTrue(set("email_me_daily_statistics_default"));

   $this->assertTrue( set("download_confirmation_enabled_display"));
   $this->assertTrue( set("download_confirmation_enabled_default"));

   $this->assertTrue( set("email_only_me_display"));
   $this->assertTrue( set("email_only_me_default"));

	$this->assertTrue(set("openSSLKeyLength"));
	$this->assertTrue(set("emailRegEx"));
   $this->assertTrue( set("webWorkersLimit"));

	// UI Settings
	$this->assertTrue(set("datedisplayformat"));
	$this->assertTrue(set("versionNumber")); // Show version number (true/false)
	$this->assertTrue(set("site_showStats")); // Show site upload/download stats (true/false)
	$this->assertTrue(set("displayUserName")); // Show 'Welcome user' (true/false)
	
	// multi file option
	$this->assertTrue(set("multiupload"));
    
    // auto complete - provides auto complete in input field for emails
    $this->assertTrue(set("autocomplete"));
   $this->assertTrue( set("autocompleteHistoryMax"));

 // Mac OS X alternative unzip application (for multiple file downloads where the default Archive Utility will not work).
   $this->assertTrue( set("mac_unzip_name"));
    $this->assertTrue(set("mac_unzip_link"));
	
	// advanced options
	// voucherreturnlock - vouchers can only be used to upload files back to the voucher creator
	$this->assertTrue(set("voucherreturnlock"));
	$this->assertTrue(set("displaydownloadsummary"));
	
	// options - stored as array in fileoptions 
	// Voucher Locked To Sender
	$this->assertTrue(set("vlts"));

	// debug settings
	$this->assertTrue(set("debug"));
	$this->assertTrue(set("dnslookup"));
	$this->assertTrue(set("client_specific_logging"));
	$this->assertTrue(set("client_specific_logging_uids"));

	// saml settings
	$this->assertTrue(set("saml_email_attribute"));
	$this->assertTrue(set("saml_name_attribute"));
	$this->assertTrue(set("saml_uid_attribute"));

	// AuP settings
	$this->assertTrue(set("AuP_default"));
	$this->assertTrue(set("AuP"));
	//set("AuP_label"] = "I accept the terms and conditions of this service"; // moved AUP to language files
	//set("AuP_terms"] = "AuP Terms and conditions"; // moved AUP to language files

	// Server settings
	$this->assertTrue(set("default_daysvalid"));
	$this->assertTrue(set("ban_extension"));
	$this->assertTrue(set("max_email_recipients"));

	$this->assertTrue(set("max_flash_upload_size"));
	$this->assertTrue(set("max_gears_upload_size"));
	$this->assertTrue(set("max_html5_upload_size"));
	$this->assertTrue(set("upload_chunk_size"));
	$this->assertTrue(set("download_chunk_size"));
    $this->assertTrue(set("html5_max_uploads"));
	
	// update max_flash_upload_size if php.ini post_max_size and upload_max_filesize is set lower
	$this->assertTrue(set("max_flash_upload_size"));
	
	$this->assertTrue(set("server_drivespace_warning"));
	
	// Advanced server settings, do not change unless you have a very good reason.
	$this->assertTrue(set("postgresdateformat"));
	$this->assertTrue(set("db_dateformat"));
	$this->assertTrue(set("crlf"));
	$this->assertTrue(set("voucherRegEx"));
	$this->assertTrue(set("voucherUIDLength"));
	$this->assertTrue(set("emailRegEx"));

	// site URL settings
	$this->assertTrue(set("site_url"));
	$this->assertTrue(set("site_simplesamlurl"));
	$this->assertTrue(set("site_authenticationSource"));
	$this->assertTrue(set("site_logouturl")); 
	$this->assertTrue(set("site_downloadurl"));
	
	$this->assertTrue(set("forceSSL"));
	$this->assertTrue(set("displayerrors"));
	// Support links
	$this->assertTrue(set("aboutURL"));
	$this->assertTrue(set("helpURL"));
	$this->assertTrue(set("HTML5URL"));
	//config['gearsURL'] = 'http://tools.google.com/gears/';

	// (absolute) file locations
	$this->assertTrue(set("site_filestore"));
	$this->assertTrue(set("site_temp_filestore")); 
	$this->assertTrue(set("site_simplesamllocation"));
	$this->assertTrue(set("log_location"));	
	
	$this->assertTrue(set("db_type"));
	$this->assertTrue(set("db_host"));
	$this->assertTrue(set("db_database"));
	$this->assertTrue(set("db_port"));
	// database username and password
	$this->assertTrue(set("db_username"));
	$this->assertTrue(set("db_password"));
	
	// cron settings
	$this->assertTrue(set("cron_exclude prefix"));
	$this->assertTrue(set("cron_shred"));
	$this->assertTrue(set("cron_shred_command"));
	$this->assertTrue(set("cron_cleanuptempdays"));
	
	// email templates section
	$this->assertTrue(set("default_emailsubject"));
	
	}
	
}
function set($item)
	{
			$CFG = new Config();
	 $config = $CFG->loadConfig();
		return isset($config[$item]);
	}
?>