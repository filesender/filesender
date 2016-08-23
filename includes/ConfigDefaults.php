<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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


  // Load default configuration
$default = array(
    'testing'   => false,   // TODO
    'debug'   => false,   // TODO
    'default_timezone' => 'Europe/London', // Default timezone to use
    'default_language' => 'en', // Default language to user
    'lang_browser_enabled' => true, // Take language from user's browser's accept-language header if provided
    'lang_userpref_enabled' => false, // Take lang from user profile
    'lang_url_enabled' => false, // Allow URL language switching (?lang=en for example)
    'lang_selector_enabled' => false, // Display language selector (requires lang_url_enabled = true)
    'lang_save_url_switch_in_userpref' => false, // Save lang switching in user preferences (requires lang_url_enabled = true and lang_userpref_enabled = true)
    'site_name' => 'FileSender', // Default site name to user
    'email_use_html' => true,   // By default, use HTML on mails
    'relay_unknown_feedbacks' => 'sender',   // Report email feedbacks with unknown type but with identified target (recipient or guest) to target owner
    'upload_display_bits_per_sec' => false, // By default, do not show bits per seconds 
    'force_ssl' => true,
    
    'auth_sp_type' => 'saml',  // Authentification type
    'auth_sp_set_idp_as_user_organization' => false,
    'auth_sp_saml_email_attribute' => 'mail', // Get email attribute from authentification service
    'auth_sp_saml_name_attribute' => 'cn', // Get name attribute from authentification service
    'auth_sp_saml_uid_attribute' => 'eduPersonTargetID', // Get uid attribute from authentification service
    'auth_sp_saml_authentication_source' => 'default-sp', // Get path  attribute from authentification service
    'auth_sp_shibboleth_email_attribute' => 'mail', // Get email attribute from authentification service
    'auth_sp_shibboleth_name_attribute' => 'cn', // Get name attribute from authentification service
    'auth_sp_shibboleth_uid_attribute' => 'eduPersonTargetId', // Get uid attribute from authentification service
    
    'auth_remote_user_autogenerate_secret' => false,
    'auth_remote_signature_algorithm' => 'sha1',
    
    'aup_default' => false,
    'aup_enabled' => false,
    'mac_unzip_name' => 'The Unarchiver',
    'mac_unzip_link' => 'http://unarchiver.c3.cx/unarchiver',
    'ban_extension' => 'exe,bat',
    
    'max_transfer_size' => 107374182400,
    'max_transfer_recipients' => 50,
    'max_transfer_files' => 30,
    'max_transfer_days_valid' => 20,
    'default_transfer_days_valid' => 10,
    'failed_transfer_cleanup_days' => 7,
    'transfer_recipients_lang_selector_enabled' => false,
    
    'max_guest_recipients' => 50,
    
    'max_legacy_file_size' => 2147483648,
    'legacy_upload_progress_refresh_period' => 5,
    'upload_chunk_size' => 5 * 1024 * 1024,
    'chunk_upload_security' => 'key',
    'download_chunk_size' => 5 * 1024 * 1024,
    
    'terasender_enabled' => true,
    'terasender_start_mode' => 'multiple',
    'terasender_worker_count' => 6,
    'stalling_detection' => true,
    
    'storage_type' => 'filesystem',
    
    'storage_filesystem_path' => FILESENDER_BASE.'/files',
    'storage_filesystem_df_command' => 'df {path}',
    
    'email_from' => 'sender',
    'email_return_path' => 'sender',
    'email_subject_prefix' => '{cfg:site_name}:',
    
    'report_bounces' => 'asap',
    'report_bounces_asap_then_daily_range' => 15 * 60,
    
    'statlog_lifetime' => 0,
    'statlog_log_user_organization' => false,
    'auditlog_lifetime' => 31,
    
    'storage_usage_warning' => 20,
    
    'report_format' => ReportFormats::INLINE,
    
    'user_page' => false,
    //'user_page' => array(
    //    'lang' => 'write',
    //    'auth_secret' => 'read',
    //    'created' => 'read'
    //),

    // Logging
    'log_facilities' => array(
        array(
            'type' => 'file',
            'path' => FILESENDER_BASE.'/log/',
            'rotate' => 'hourly'
        )
    ),
    
    'site_logouturl' => function() {
        return Config::get('site_url').'?s=logout';
    },
    
    'show_storage_statistics_in_admin' => true,
    
    'transfer_options' => array(
        'email_me_copies' => array(
            'available' => true,
            'advanced' => true,
            'default' => false
        ),
        'email_me_on_expire' => array(
            'available' => true,
            'advanced' => false,
            'default' => true
        ),
        'email_upload_complete' => array(
            'available' => true,
            'advanced' => false,
            'default' => false
        ),
        'email_download_complete' => array(
            'available' => true,
            'advanced' => false,
            'default' => true
        ),
        'email_daily_statistics' => array(
            'available' => true,
            'advanced' => true,
            'default' => false
        ),
        'email_report_on_closing' => array(
            'available' => true,
            'advanced' => false,
            'default' => true
        ),
        'enable_recipient_email_download_complete' => array(
            'available' => true,
            'advanced' => true,
            'default' => true
        ),
        'add_me_to_recipients' => array(
            'available' => true,
            'advanced' => false,
            'default' => false
        ),
        'get_a_link' => array(
            'available' => true,
            'advanced' => false,
            'default' => true
        ),
        'redirect_url_on_complete' => array(
            'available' => false,
            'advanced' => true,
            'default' => ''
        ),
    ),
    
    'guest_options' => array(
        'email_upload_started' => array(
            'available' => true,
            'advanced' => false,
            'default' => true
        ),
        'email_upload_page_access' => array(
            'available' => true,
            'advanced' => false,
            'default' => false
        ),
        'valid_only_one_time' => array(
            'available' => true,
            'advanced' => true,
            'default' => false
        ),
        'does_not_expire' => array(
            'available' => true,
            'advanced' => true,
            'default' => false
        ),
        'can_only_send_to_me' => array(
            'available' => true,
            'advanced' => false,
            'default' => false
        ),
        'email_guest_created' => array(
            'available' => false,
            'advanced' => true,
            'default' => true
        ),
        'email_guest_created_receipt' => array(
            'available' => false,
            'advanced' => true,
            'default' => true
        ),
        'email_guest_expired' => array(
            'available' => false,
            'advanced' => true,
            'default' => true
        ),
    ),
);
