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
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

// ---------------------------------------------
//             README / 2014-09-11
// ---------------------------------------------
// 
// This is a sample of configuration file for Filesender
// --
// The configuration list is available at [todo: wiki URL]
//
// To make filesender work, you need first to create a file 'config/config.php',
// and at least to fill the following configuration parameters:


// ---------------------------------------------
//              General settings
// ---------------------------------------------
// 
//$config['site_url'] = 'https://127.0.0.1/filesender';                // String, URL of the application
// 
//$config['admin'] = 'root@localhost.localdomain'; // String, UID's (from  $config['saml_uid_attribute']) 
//                                                 // that have Administrator permissions

//$config['admin_email'] ='root@localhost.localdomain';   // String, email  address(es, separated by ,) 
//                                                        // to receive administrative messages (low disk  space warning)

//$config['email_reply_to'] ='';    // String, default no-reply email  address

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------
//   DB: use one of the below depending on your database preference
// -----------------------------------------------------------------

// ---------------------------------------------
// MariaDB/MySQL DB configuration
// ---------------------------------------------
//$config["db_type"] ='mysql';
//$config['db_host'] ='localhost';
//$config['db_database'] ='filesender';
//$config['db_username'] ='filesender';
//$config['db_password'] ='';
//$config['db_username_admin'] = 'filesenderadmin';
//$config['db_password_admin'] = '';

// ---------------------------------------------
// PostgreSQL DB configuration
// ---------------------------------------------
//$config["db_type"] ='pgsql';     
//$config['db_host'] ='localhost';       
//$config['db_database'] ='filesender';  
//$config['db_username'] ='filesender';  
//$config['db_password'] ='';            

// -----------------------------------------------------------------
// -----------------------------------------------------------------
// -----------------------------------------------------------------

// ---------------------------------------------
//              SAML configuration
// ---------------------------------------------
// NOTE: These MUST have trailing slash
//$config['auth_sp_saml_simplesamlphp_url'] ='https://127.0.0.1/simplesaml/';     // Url of simplesamlphp
$config['auth_sp_saml_simplesamlphp_location'] ='/opt/filesender/simplesaml/';   // Location of simplesamlphp libraries

//      ----------------------------
//      -------- [optional] --------
//      ----------------------------
//
// If you want to overide the SAML simplephp configuration defaults parameter,
// uncoment and edit the following lines
// 
// // Authentification type ('saml' or 'shibboleth')
// $config['auth_sp_type'] = 'saml';
// 
// // Get email attribute from authentication service
// $config['auth_sp_saml_email_attribute'] = 'mail';
// 
// // Get name attribute from authentication service
// $config['auth_sp_saml_name_attribute'] = 'cn';
// 
// $config['auth_sp_saml_uid_attribute'] = 'pairwise-id';
//
// // Attribute to use for entitlement. Usually eduPersonEntitlement or isMemberOf
// $config['auth_sp_saml_entitlement_attribute'] = 'eduPersonEntitlement';
//
// // Also make sure the above addtional attribute is actually retrieved
// $config['auth_sp_additional_attributes'] = array('eduPersonEntitlement');
//
// // What entitlement value denotes the admin privilege
// $config['auth_sp_saml_admin_entitlement'] = 'urn:x-geant:eduteams.org:filesender:admin';
//
// // Get path  attribute from authentication service
// $config['auth_sp_saml_authentication_source'] = 'default-sp';

// If you want to use hard coded user/pass in /opt/filesender/simplesaml/config/authsources.php for testing.
// $config['auth_sp_saml_authentication_source'] ="example-userpass";

// --------------------------------------------------
//    TeraSender high speed upload module             
// --------------------------------------------------

$config['terasender_enabled'] = true;    	// 
$config['terasender_advanced'] = true;    	// Make #webworkers configurable in UI.  Switched this on to make it easy 
						// to determine optimal number for terasender_worker_count when going in production.  
						// The useful number of maximum webworkers per browser changes nearly for each browser release.
$config['terasender_worker_count'] = 5;   	// Number of web workers to launch simultaneously client-side when starting upload
$config['terasender_start_mode'] = 'single';	// I think I prefer to show a nice serial predictable upload process


// ---------------------------------------------
//              File locations (or storage?)
// ---------------------------------------------

$config['storage_type'] = 'filesystem';
$config['storage_filesystem_path'] = '/opt/filesender/filesender/files';

// ---------------------------------------------
//       this is how the test suite wants things.
// ---------------------------------------------

$testing = false;
if( $testing ) {
  $config['testsuite_run_locally'] = true;
  $config['TESTSUITE_LOCAL'] = '1';
  $config['auth_sp_type'] = 'fake';
  $config['auth_sp_fake_authenticated'] = true;
  $config['auth_sp_fake_uid'] = 1;
  $config['auth_sp_fake_email'] = 'root@localhost.localdomain';
  $config['PUT_PERFORM_TESTSUITE'] = '';

}
