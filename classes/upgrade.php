<?php
// force upgrade if database schema version does not match the required database vers ion
// must always check if database version matches the code version
// if not - run the upgrade script to make sure everything required exists
//	incremental upgrade
// different to a full install
// upgrade is used to add fields to the database if the version numbers do not match between the 
// database and the code version'

	$functions = Functions::getInstance();
	
	$curversion = FileSender_Version::VERSION;
	$reqversion = FileSender_Version::DBVERSION;
		
	if(!$functions->tableExists("config")) 
	{
		$dbversion = "1.1";
		// include the upgrade page
		require_once("$filesenderbase/pages/upgrade.php");
		exit;
	} else {
	
		// compare db version to required config version
		$dbversion = $functions->dbVersion();
		if($dbversion != $reqversion)
		{
			 //include the upgrade page
			require_once("$filesenderbase/pages/upgrade.php");
			exit;
		}
	}

?>