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
 *  notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *  names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.
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

if(PHP_INT_SIZE !== 8) {
    die('FileSender requires a 64-bit PHP installation to work. Please contact your administrator.');
}

define('FILESENDER_BASE', dirname(dirname(__FILE__)));

// Disable session cache
session_cache_limiter('nocache');

// Start session if necessary
if(!session_id()) {
    // start new session and mark it as valid because the system is a trusted source
    session_start();
    $_SESSION['valid'] = true;
}

// Include classes autoloader
require_once(FILESENDER_BASE.'/classes/autoload.php');

// Set default timezone
date_default_timezone_set(Config::get('default_timezone'));

// Ensure HTTPS if needed
Utilities::forceHTTPS();
//require_once(FILESENDER_BASE.'/includes/EnsureHTTPS.php');

// Handle magic quoting (maybe deprecated now ?)
if(get_magic_quotes_gpc()) {
    $_POST = array_map('stripslashes', $_POST);
    $_GET = array_map('stripslashes', $_GET);
};

// Output is all UTF8
header('Content-Type: text/html; charset=UTF-8');

// We expect UTF8 data from the client
//header('Accept: ');
