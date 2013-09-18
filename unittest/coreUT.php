<?php
// Tests for Filsender Version 1.6
require_once('../simpletest/autorun.php');
require_once('../classes/_includes.php');
require_once('utconfig.php');

class testFileSender_coreUT extends UnitTestCase {
	 function test_coreUT()  
    {
		global $config, $default,$obj;
		
		// ----- GENERAL --------
		
	// ----- FORMAT BYTES START -------- 
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
	// ----- FORMAT BYTES END -------- 
	
	
	// ----- ENSURE SANE FILE UID START -------- 
	$testGUID = $obj["filevoucheruid"];
	$testInsaneGUID = " xxx-xx";
	$this->assertTrue(preg_match($config['voucherRegEx'], $testGUID) and strLen($testGUID) == $config['voucherUIDLength']);
	$this->assertEqual(ensureSaneFileUid($testGUID),$testGUID );
	// TODO: Remove trigger error in ensureSaneFileUid as it block false return
	//$this->assertFalse(ensureSaneFileUid($testInsaneGUID));
	// ----- ENSURE SANE FILE UID END -------- 
	
	
	// ----- GET GUID START -------- 		
	// Test that getGUID() returns areal GUID
	// Assumes ENSURE SANE FILE UID is ok
	$testGUID = getGUID();
	$this->assertEqual(ensureSaneFileUid($testGUID),$testGUID );
	// ----- GET GUID END -------- 
	
	
	// TEST: get open SSL getOpenSSLKey
	
	
	// ----- SANITISE FILENAME START -------- 
	// NTFS / ? < > \ : * | ”
	// MAC :
	$originalFilename = "abcdefghijklmnopqrstuvwxyz1234567890-=!@#$%^&*()_+[]{};':,./<>?~`";
	$sanitisedFilename = "abcdefghijklmnopqrstuvwxyz1234567890-=!@#$%^&*()_+[]{};':,./<>?~`";
	$this->assertEqual(sanitizeFilename($originalFilename),$sanitisedFilename);
	// ----- SANITISE FILENAME END -------- 
	
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
	$validURL = "http://filesender.org";
	$this->assertEqual($DB_Input_Checks->checkURL($validURL),$validURL);
	
	// returns false for invalid url
	$invalidURL = "h://filesender.org";
	$this->assertFalse($DB_Input_Checks->checkURL($invalidURL));
	
	// TEST: IP
	$val = "20.255.05.5";
	$result = "020.255.005.005";
	// TODO: CHECK
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
	
	// ----- VERSION START -------- 
	// TEST: check version function is correct 
	// NOTE: Set correct version in default['version']
	
	$FileSender_Version = new FileSender_Version();
	$testversion = $default["version"];
	$this->assertEqual($FileSender_Version->compareVersion($testversion),0);
	$testversion = "1.6-multiupload-0";
	$this->assertEqual($FileSender_Version->compareVersion($testversion),-1);
	$testversion = "1.6-multiupload-9999999";
	$this->assertEqual($FileSender_Version->compareVersion($testversion),1);
	// ----- VERSION END -------- 
	}
	
	
	
}
?>