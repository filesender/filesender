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
$config['site_url'] = 'https://terasender.uninett.no/branches/filesender-2.0/';                // String, URL of the application
// 
$config['admin'] = 'meijer@rnd.feide.no';            // String, UID's (from  $config['saml_uid_attribute']) 
                                    // that have Administrator permissions

$config['admin_email'] ='jan.meijer@uninett.no';       // String, email  address(es, separated by ,) 
                                    			// to receive administrative messages (low disk  space warning)

$config['session_cookie_path'] = 'https://terasender.uninett.no/';

// ---------------------------------------------
//              Language settings
// ---------------------------------------------
$config['lang_browser_enabled'] = true;    				// default is false.  Shows language based on user's browser setting.
$config['lang_selector_enabled'] = true;    				// default is false.  Enables explicit language selection in UI
$config['lang_url_enabled'] = true;    				// default is false.  Needed to make lang_selector_enabled and lang_browser_enabled to work. Also needed to allow translation of emails. 




// ---------------------------------------------
//              Email settings
// ---------------------------------------------

$config['email_use_html'] = true;    				// true or false
$config['email_from'] = 'no-reply@uninett.no';    		// either 'sender' or an email address
$config['email_from_name'] = 'UNINETT FileSender - beta 2.0';	// pretty name with the From: address
$config['email_reply_to'] ='sender';    			// either 'sender' or an email address
// $config['email_reply_to_name'] = 'pretty name';    		// pretty name in case email_reply_to is a configured email address
$config['email_return_path'] = 'no-reply@uninett.no';				// either 'sender' or an email address

// --------------------------------------------------
//              Web UI settings
// --------------------------------------------------

$config['force_legacy_mode'] = false;			// for testing legacy non-HTML5 mode

$config['autocomplete'] = 10;					// show previously used email addresses in To: fields.  Set to positive number to enable.  Number indicates how many hits are shown to user.  Addresses are stored in user preferences.  When you sent to another set of recipients, will remove from list and add them at beginning of list.  So the more you write to them the longer they stay.  The longer you don't write to someone the lower they get on the list until they drop off.  Seems to work pretty well at RENATER.  
$config['autocomplete_max_pool'] = 100;				// how many values are stored in database.  Default is 5.
$config['autocomplete_min_characters'] = 2;		// Optional.  Default 3.  How many characters to type before autocomplete list is triggered 

$config['transfer_options'] = array(
		'add_me_to_recipients' => array(
			'available' => true,
			'advanced' => false,
			'default' => true
		),
		'get_a_link' => array(
			'available' => true,
			'advanced' => false,
			'default' => false
		),
		'email_upload_complete' => array(
			'available' => true,
			'advanced' => false,
			'default' => true
		),
		'email_me_copies' => array(
			'available' => true,
			'advanced' => true,
			'default' => false
		)
		
);

// --------------------------------------------------
//    TeraSender high speed upload module             
// --------------------------------------------------

$config['terasender_enabled'] = true;    				// 
$config['terasender_advanced'] = true;    				// 



// --------------------------------------------------
//              Authenticated user transfer settings
// --------------------------------------------------

// basic functionality tested.  Need to decide if "today" is part of the 10 days valid!
// should do something to indicate at what time files will normally be deleted!

$config['max_transfer_days_valid'] = 10;    				// what user sees in date picker for expiry date. If not set this defaults to 20.  
$config['default_transfer_days_valid'] = 5;    				// Default expiry date as per date picker in upload UI.  Most users will not change this.  If not set, this defaults to 10.


// ---------------------------------------------
//              Guest transfer settings
// ---------------------------------------------

//$config['default_guest_days_valid'] = ;    				// if not set, this defaults default_transfer_days_valid
//$config['max_guest_days_valid'] = ;    				// if not set, this defaults to max_days_valid
//$config['guest_options'] = ;    				// set of options available for guest users
//$config['max_guest_recipients'] = 50;    				// max no. of recipients a transfer can have.  Defaults to 50.


// ---------------------------------------------
//              DB configuration
// ---------------------------------------------
$config["db_type"] ='pgsql';       // String, pgsql or mysql
$config['db_host'] ='localhost';       // String, database host 
$config['db_database'] ='filesender20';   // String, database name
$config['db_username'] ='filesender';   // String, database username
$config['db_password'] ='yourdatabasepassword';   // String, database password

// ---------------------------------------------
//              SAML configuration
// ---------------------------------------------

$config['auth_sp_saml_simplesamlphp_url'] ='/branches/filesender-2.0/simplesaml/';        // Url of simplesamlphp
$config['auth_sp_saml_simplesamlphp_location'] ='/usr/local/filesender/fs20-simplesaml/';   // Location of simplesamlphp libraries


// ---------------------------------------------
//              File locations (or storage?)
// ---------------------------------------------

$config['storage_type'] = 'filesystem';
$config['storage_filesystem_path'] = '/data/branches/filesender-2.0/files';

    //'storage_filesystem_df_command' => 'df {path}',



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
// // Get uid attribute from authentication service.  Usually eduPersonTargetedId or eduPersonPrincipalName
$config['auth_sp_saml_uid_attribute'] = 'eduPersonPrincipalName';
// 
// // Get path  attribute from authentication service
// $config['auth_sp_saml_authentication_source'] = 'default-sp';
