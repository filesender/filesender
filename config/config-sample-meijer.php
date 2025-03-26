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

$config['session_cookie_path'] = '/';


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
$config['email_newline'] = "\r\n";				// if emails are mangled replace this with \n.  Make sure to put it in double quotes!!



// --------------------------------------------------
//              Web UI settings
// --------------------------------------------------

$config['force_legacy_mode'] = false;			// for testing legacy non-HTML5 mode

$config['autocomplete'] = 10;					// show previously used email addresses in To: fields.  Set to positive number to enable.  Number indicates how many hits are shown to user.  Addresses are stored in user preferences.  When you sent to another set of recipients, will remove from list and add them at beginning of list.  So the more you write to them the longer they stay.  The longer you don't write to someone the lower they get on the list until they drop off.  Seems to work pretty well at RENATER.  
$config['autocomplete_max_pool'] = 1;				// how many values are stored in database.  Default is 5.
$config['autocomplete_min_characters'] = 2;		// Optional.  Default 3.  How many characters to type before autocomplete list is triggered 
$config['upload_display_bits_per_sec'] = false;		



// --------------------------------------------------
//              Transfer settings
// --------------------------------------------------

$config['max_transfer_size'] = 2107374182400;

$config['transfer_options'] = array(

		// Sender gets copies of all emails sent to everyone.  "spam me plenty" option.
		'email_me_copies' => array(
			'available' => true,
			'advanced' => true,
			'default' => false
		),

		// Sender gets an email once an upload is complete.  Practical for long uploads
		'email_upload_complete' => array(
			'available' => true,
			'advanced' => true,
			'default' => true
		),

		// By default send the Sender a daily overview of what happened to his/her transfers
		// rather than sending individual emails for each event (download).
		'email_daily_statistics' => array(
			'available' => true,
			'advanced' => false,
			'default' => true
		),

		// Sender gets an email when someone has downloaded (parts of) a transfer
		'email_download_complete' => array(
			'available' => true,
			'advanced' => true,
			'default' => false
		),
		
		// Always send the sender an audit report when a transfer expires
		// Because "default" is set to "true" and "available" to "false" the user can not untick this.
		// If you want this to be a user choice, set 'available' to 'true'
		//
		// I made this choice because I want to be able to delete the audit logs pertaining to a transfer
		// after a relatively short time.  So I want to be able to tell a user "here is your audit log, this
		// shows who downloaded your files when.  I will now delete this information, if you wish to keep it
		// then do not delete this email.
		'email_report_on_closing' => array(
			'available' => false,
			'advanced' => false,
			'default' => true
		),

		// Never allow a recipient to receive an email once their download is complete
		'enable_recipient_email_download_complete' => array(
			'available' => false,
			'advanced' => false,
			'default' => false
		),
		
		// include the sender as a recipient
		'add_me_to_recipients' => array(
			'available' => true,
			'advanced' => false,
			'default' => true
		),

		// Allow a user to only upload a file and take care of download URL distribution him/herself.
		'get_a_link' => array(
			'available' => true,
			'advanced' => false,
			'default' => false
		)
);

// --------------------------------------------------
//    TeraSender high speed upload module             
// --------------------------------------------------

$config['terasender_enabled'] = true;    	// 
$config['terasender_advanced'] = true;    	// Make #webworkers configurable in UI.  Switched this on to make it easy 
						// to determine optimal number for terasender_worker_count when going in production.  
						// The useful number of maximum webworkers per browser changes nearly for each browser release.
$config['terasender_worker_count'] = 10;   	// Number of web workers to launch simultaneously client-side when starting upload
$config['terasender_start_mode'] = single;	// I think I prefer to show a nice serial predictable upload process



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
//$config['max_guest_days_valid'] = ;    				// if not set, this defaults to max_transfer_days_valid
//$config['guest_options'] = '';    				// set of options available for guest users
//$config['max_guest_recipients'] = 50;    				// max no. of recipients a transfer can have.  Defaults to 50.

$config['guest_options'] = array(

                // Guest invitation creator gets an email when guest starts upload.
                'email_upload_started' => array(
                        'available' => true,
                        'advanced' => true,
                        'default' => false
                ),

                // Guest invitation creator gets an email when guest accesses upload page.
                'email_upload_page_access' => array(
                        'available' => true,
                        'advanced' => true,
                        'default' => false
                ),

		// A guest invitation can only be used for one transfer
                'valid_only_one_time' => array(
                        'available' => true,
                        'advanced' => false,
                        'default' => true
		),

		// This guest invitation will not expire and can be used for unlimited transfers.
		// Combine with "can_only_send_to_me" to create a permanent upload link users can 
		// include in email signatures
                'does_not_expire' => array(
                        'available' => true,
                        'advanced' => true,
                        'default' => false
		),

		// Guests can only use their access to send transfers to the creator of the guest invitation
                'can_only_send_to_me' => array(
                        'available' => true,
                        'advanced' => false,
                        'default' => true
		),
);



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

//$config['auth_sp_saml_simplesamlphp_url'] ='/branches/filesender-2.0/simplesaml/';        // Url of simplesamlphp
$config['auth_sp_saml_simplesamlphp_url'] ='/simplesaml/';        // Url of simplesamlphp
//$config['auth_sp_saml_simplesamlphp_location'] ='/usr/local/filesender/fs20-simplesaml/';   // Location of simplesamlphp libraries
$config['auth_sp_saml_simplesamlphp_location'] ='/usr/local/filesender/simplesaml/';   // Location of simplesamlphp libraries


// ---------------------------------------------
//              File locations (or storage?)
// ---------------------------------------------

$config['storage_type'] = 'filesystem';
$config['storage_filesystem_path'] = '/data/branches/filesender-2.0/files';

    //'storage_filesystem_df_command' => 'df {path}',

// ---------------------------------------------
//              Multi-site configs
// ---------------------------------------------

//
// Can't write to datbase?  Functionality still exists?
//$config['config_overrides'] = array( 'site_name_in_header' => 'bool', 'site_name' => array('type' => 'string', 'validator' => 'is_string'));


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
$config['auth_sp_saml_uid_attribute'] = 'pairwise-id';

// 
// // Get path  attribute from authentication service
$config['auth_sp_saml_authentication_source'] = 'default-sp';
