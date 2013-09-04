<?php
// Tests for Filsender Version 1.6
require_once('../simpletest/autorun.php');
require_once('../classes/_includes.php');

class testFileSender_coreUT extends UnitTestCase {
	 function test_coreUT()  
    {
		// TODO: Fix ensure sans file uid to return bool
		// Test ensureSaneFileUid accepts a GUID as specified
		//$testGUID = "6bf0eeed-e341-a8c8-22a6-00004e587673";
		//$this->assertTrue(ensureSaneFileUid($testGUID));
		
		// Test ensureSaneFileUid returns false if not a correct guid
		//$testGUID = "xxxxxx-xxx-xx-x-x";
		//$this->assertFalse(ensureSaneFileUid($testGUID));
		
		// Test ensureSaneFileUid returns false if no guid empty
		//$testGUID = "";
		//$this->assertTrue(ensureSaneFileUid($testGUID));
		
		// Test that getGUID() returns areal GUID
		$testGUID = getGUID();
		$this->assertEqual(ensureSaneFileUid($testGUID),$testGUID);
		
		// NTFS / ? < > \ : * | ”
		// MAC :
		//$originalFilename = "abcdefghijklmnopqrstuvwxyz1234567890-=!@#$%^&*()_+[]{};':,./<>?~`";
		//$sanitisedFilename = "abcdefghijklmnopqrstuvwxyz1234567890-=!@#$%^&*()_+[]{};'_,./<>?~`";
		//$this->assertEqual(sanitizeFilename($originalFilename),$sanitisedFilename);
		// ----- GENERAL --------
	
	// TEST: format Bytes functions formatBytes
	// test for Kb, Mb, Gb - default precision is 2
	$val = 1000; $result = "1000 Bytes"; 	
	$this->assertEqual(formatBytes($val,2),$result);
	$val = 10000; $result = "9.77 kB"; 	
	$this->assertEqual(formatBytes($val,2),$result);
	$val = 100000000; $result = "95.37 MB"; 	
	$this->assertEqual(formatBytes($val,2),$result);
	$val = 100000000000; $result = "93.13 GB"; 	
	$this->assertEqual(formatBytes($val,2),$result);
	$val = 100000000000000; $result = "90.95 TB"; 	
	$this->assertEqual(formatBytes($val,2),$result);
	// TESWT: get a valid GUID
	// Test that getGUID() returns areal GUID
	$testGUID = getGUID();
	$this->assertEqual(ensureSaneFileUid($testGUID),$testGUID );
	
	// TEST: get open SSL getOpenSSLKey
	
	// TEST: sanitize a Filename
	
	// TEST: ensure sane FIle UID ensureSaneFileUid
	
	// TEST: ensureSaneOpenSSLKey	
	
	// ----- DATABASE --------
	// TEST: DB connects
	
	// TEST: DB error result
	
	
	// ----- VALIDATE --------
	$DB_Input_Checks = new DB_Input_Checks();
	// TEST: Email
	// Check function returns url if it is valid
	$val = "test@filesender.org";
	$this->assertEqual($DB_Input_Checks->checkEmail($val),$val);
	// TODO: Add other invalid email address formats
	// returns false for invalid url
	$val = "testfilesender";
	$this->assertFalse($DB_Input_Checks->checkEmail($val));

	
	// TEST: URL
	// Check function returns url if it is valid
	$val = "http://filesender.org";
	$result = $val;
	$this->assertEqual($DB_Input_Checks->checkURL($val),$result);
	
	// returns false for invalid url
	$val = "h://filesender.org";
	$this->assertFalse($DB_Input_Checks->checkURL($val));
	
	// TEST: IP
	// TODO: CHECK
	
	$val = "20.255.05.5";
	$result = "020.255.005.005";
	//$this->assertEqual($DB_Input_Checks->checkIP($val),$result);
	
	$val = "255.255.255.255.54";
	$result = "0.0.0.0";
	$this->assertEqual($DB_Input_Checks->checkIP($val),$result);
	
	//TODO: FIx IPV6
	// TEST: IPv6
	$val = "20.255.05.5";
	$result = "020.255.005.005";
	//$this->assertEqual($DB_Input_Checks->checkIPV6($val),$result);
	
	$val = "255.255.255.255.54";
	$result = "::";
	//$this->assertEqual($DB_Input_Checks->checkIPV6($val),$result);
	
	// ----- LANGUAGE --------
	// TEST: Load a language file
	
	// TEST: Load a custom language file
	
	// TEST: Access language term
	
	// TEST: get Client Language
	
	// ----- LOG --------
	// TEST: Save/check  a log file
	
	// TEST: Check if log saves to DB
	
	// ----- VERSION --------
	// TEST: check version
	
	}
	
	
	
}
?>