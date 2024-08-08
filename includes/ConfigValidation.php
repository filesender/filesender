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

// Require environment (fatal)
if(!defined('FILESENDER_BASE')) die('Missing environment');

if(!file_exists(FILESENDER_BASE.'/config/config.php'))
    die('Configuration file not found');

ConfigValidator::addCheck('site_url', 'string');
ConfigValidator::addCheck('client_ip_key', 'string|array');

ConfigValidator::addCheck('admin', 'string|array');
ConfigValidator::addCheck('admin_email', 'string');
ConfigValidator::addCheck('email_reply_to', 'string');

ConfigValidator::addCheck('db_type', 'string');
ConfigValidator::addCheck('db_host', 'string');
ConfigValidator::addCheck('db_database', 'string');

ConfigValidator::addCheck('chunk_upload_security', 'string', function($value, &$error) {
    if(in_array($value, array('auth', 'key'), true))
        return true;
    
    $error = 'chunk_upload_security must be either "auth" or "key"';
    return false;
});

ConfigValidator::addCheck('lang_selector_enabled', function($value, &$error) {
    if(!$value) return true;
    if(Config::get('lang_url_enabled')) return true;
    
    $error = 'lang_url_enabled must be set to true if lang_selector_enabled is, otherwise the language selector won\'t work';
    return false;
});

ConfigValidator::addCheck('default_language', function($lang, &$error) {
    if(!$lang) return true;
    
    $lang = Lang::realCode($lang);
    $available = Lang::getAvailableLanguages();
    
    if(array_key_exists($lang, $available)) return true;
    
    $error = 'default_language must be one of the available languages defined in locale.php ('.implode(', ', array_keys($available)).')';
    return false;
});

ConfigValidator::addCheck('allow_transfer_expiry_date_extension', function($pattern, &$error) {
    if(!$pattern) return true;
    
    if(!is_array($pattern)) $pattern = array($pattern);
    
    if(!is_int($pattern[0]) || $pattern[0] <= 0) {
        $error = 'allow_transfer_expiry_date_extension expects at least one positive non-zero integer';
        return false;
    }
    
    while($ext = array_shift($pattern)) {
        if(is_int($ext)) {
            if($ext <= 0) {
                $error = 'allow_transfer_expiry_date_extension integers must be positive and non-zero';
                return false;
            }
            
        } else if(is_bool($ext) && $ext) {
            if(count($pattern)) {
                $error = 'only last value in allow_transfer_expiry_date_extension can be true';
                return false;
            }
            
        } else {
            $error = 'allow_transfer_expiry_date_extension must only contain positive non-zero integers or a true value at the end';
            return false;
        }
    }
    
    return true;
});

ConfigValidator::addCheck('terasender_worker_count', 'int', function($value, &$error) {
    if($value > 0 && $value <= 30) return true;
    
    $error = 'terasender_worker_count must be an integer between 1 and 30';
    return false;
});

ConfigValidator::run();
