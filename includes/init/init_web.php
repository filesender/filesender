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



// Start session if necessary
if(!session_id()) {
    // Disable session cache
    session_cache_limiter('nocache');
    
    // start new session and mark it as valid because the system is a trusted source
    
    // Set session cookie options
    $site_url_parts = parse_url(Config::get('site_url'));
    
    // Use configured path for cookie if set
    $session_cookie_path = Config::get('session_cookie_path');
    if(!$session_cookie_path) $session_cookie_path = $site_url_parts['path'];
    
    $isSecure = Config::get('force_ssl') || Utilities::httpsInUse();
    session_set_cookie_params( array(
        'lifetime' => 0,                            // Cookie lives as long as browser isn't closed
        'path'     => $session_cookie_path,         // It is only valid for the filesender app
        'domain'   => $site_url_parts['host'],      // and only for the precise domain
        'secure'   => $isSecure,                    // It uses secure mode if ssl forced or in use
        'httponly' => true,                         // not reachable through javascript
        'samesite' => 'Strict'                      // strict cookie settings
    ));
}


// Ensure HTTPS if needed
GUI::forceHTTPS();

// Sanitize all input variables
$_GET = Utilities::sanitizeInput($_GET);
$_POST = Utilities::sanitizeInput($_POST);
$_COOKIE = Utilities::sanitizeInput($_COOKIE);
$_REQUEST = Utilities::sanitizeInput($_REQUEST);

// Output is all UTF8
header('Content-Type: text/html; charset=UTF-8');

// Validate config on the fly
require_once(FILESENDER_BASE.'/includes/ConfigValidation.php');
