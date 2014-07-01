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

$filesenderBase = dirname(dirname(__FILE__));
$errorMsg = '';

if (file_exists("$filesenderBase/config/config.php")) {
    require_once("$filesenderBase/config/config.php");
} else {
    $errorMsg .= '<li>Configuration file is missing.</li>';
    printErrorAndExit($errorMsg);
}

$CFG = config::getInstance();
global $config;
$config = $CFG->loadConfig(); // Use _global $config in all functions.

// The following list must be updated any time a config setting is added/removed.
$requiredConfigFields = array(
    // General
    'admin',
    'adminEmail',
    'Default_TimeZone',
    'site_defaultlanguage',
    'site_name',
    'noreply',
    'customCSS',

    // UI
    'datedisplayformat',
    'versionNumber',
    'site_showStats',
    'upload_box_default_size',
    'upload_display_MBps',

    // Auto complete
    'autocomplete',
    'autocompleteHistoryMax',

    // Debug
    'debug',
    'displayerrors',
    'dnslookup',
    'client_specific_logging',
    'client_specific_logging_uids',

    // SAML
    'saml_email_attribute',
    'saml_name_attribute',
    'saml_uid_attribute',

    // AuP
    'AuP_default',
    'AuP',

    // Zip
    'mac_unzip_name',
    'mac_unzip_link',

    // Server
    'default_daysvalid',
    'ban_extension',
    'max_email_recipients',
    'max_flash_upload_size',
    'max_html5_upload_size',
    'upload_chunk_size',
    'download_chunk_size',
    'html5_max_uploads',
    'max_flash_upload_size',
    'server_drivespace_warning',

    // Terasender
    'terasender',
    'terasenderadvanced',
    'terasender_chunksize',
    'terasender_workerCount',
    'terasender_jobsPerWorker',

    // Email flow
    'email_me_copies_display',
    'email_me_copies_default',
    'upload_complete_email_display',
    'upload_complete_email_default',
    'inform_download_email_display',
    'inform_download_email_default',
    'email_me_daily_statistics_display',
    'email_me_daily_statistics_default',
    'download_confirmation_enabled_display',
    'download_confirmation_enabled_default',
    'add_me_to_recipients_display',
    'add_me_to_recipients_default',

    // Advanced server settings
    'db_dateformat',
    'crlf',
    'voucherRegEx',
    'voucherUIDLength',
    'openSSLKeyLength',
    'emailRegEx',
    'webWorkersLimit',

    // Site URL
    'forceSSL',

    // Support links
    'aboutURL',
    'helpURL',

    // File locations
    'site_filestore',
    'site_temp_filestore',
    'site_simplesamllocation',
    'log_location',

    // Database
    'db_type',
    'db_host',
    'db_database',
    'db_port',
    'db_username',
    'db_password',

    // Cron
    'cron_exclude prefix',
    'cron_shred',
    'cron_shred_command',
    'cron_cleanuptempdays'
);

foreach ($requiredConfigFields as $field) {
    if (!isset($config[$field])) {
        $errorMsg .= '<li>Missing field: ' . $field . '.</li>';
    }
}

printErrorAndExit($errorMsg);

function printErrorAndExit($errorMsg)
{
    if ($errorMsg != '') {
        echo 'Found the following error(s) in the configuration file - please contact your administrator:<br /><ul>' . $errorMsg . '</ul>';
        exit;
    }
}

