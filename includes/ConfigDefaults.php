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
    'default_timezone' => 'Europe/London', // Default timezoen to use
    'default_language' => 'en', // Default language to user
    'site_name' => 'Filesender', // Default site name to user
    'email_use_html' => true,   // By default, use HTML on mails
    'upload_display_bits_per_sec' => false, // By default, do not show bits per seconds 
    
    'auth_sp_type' => 'saml',  // Authentification type
    'auth_sp_saml_email_attribute' => 'mail', // Get email attribute from authentification service
    'auth_sp_saml_name_attribute' => 'cn', // Get name attribute from authentification service
    'auth_sp_saml_uid_attribute' => 'eduPersonTargetId', // Get uid attribute from authentification service
    'auth_sp_saml_authentification_source' => 'default-sp', // Get path  attribute from authentification service
    'auth_sp_shibboleth_email_attribute' => 'mail', // Get email attribute from authentification service
    'auth_sp_shibboleth_name_attribute' => 'cn', // Get name attribute from authentification service
    'auth_sp_shibboleth_uid_attribute' => 'eduPersonTargetId', // Get uid attribute from authentification service
    
    'aup_default' => false,
    'aup_enabled' => false,
    'mac_unzip_name' => 'The Unarchiver',
    'mac_unzip_link' => 'http://unarchiver.c3.cx/unarchiver',
    'ban_extension' => 'exe,bat',
    'max_email_recipients' => 50,
    
    'max_legacy_upload_size' => 2147483648,
    'max_html5_upload_size' => 107374182400,
    'upload_chunk_size' => 2000000,
    'download_chunk_size' => 5242880,
    'max_files_per_transfer' => 30,
    
    'terasender_enabled' => true,
    'terasender_start_mode' => 'multipler',
    
    'storage_type' => 'filesystem',
    'storage_filesystem_path' => FILESENDER_BASE.'/files',
    'statlog_lifetime' => 31,
    'auditlog_lifetime' => 0,
    
    'report_format' => ReportTypes::HTML,
);