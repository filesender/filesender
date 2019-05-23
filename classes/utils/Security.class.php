<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2019, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}



/**
 * Utility functions for security
 */
class Security
{

    /**
     *  
     * Apache or NGINX may have been configured to already have
     * some security headers enabled. This method can add some
     * more defaults or update things if it can to ensure a site
     * policy.
     */
    public static function addHTTPHeaders()
    {
        $v = Config::get('header_x_frame_options');
        if( $v != 'sameorigin' && $v != 'deny' && $v != 'none' ) {
            throw new ConfigBadParameterException('The setting header_x_frame_options should be one of the valid values.');
        }
        if( $v != 'none' ) {
            header( 'X-Frame-Options: ' . $v, false );
        }
        
    }

    private static $filesender_csrf_protector_logger = null;
    
    /**
     * Validate against CSRF using the current configured methods
     */
    public static function validateAgainstCSRF( $canReturnJSON = false )
    {
        if( Config::get('owasp_csrf_protector_enabled') ) {
            include_once('../lib/vendor/owasp/csrf-protector-php/libs/csrf/csrfprotector.php');
            if( !self::$filesender_csrf_protector_logger ) {
                self::$filesender_csrf_protector_logger = new FileSendercsrfProtectorLogger();
            }
            $length = null; // no change
            $action = null; // no change
            $logger = self::$filesender_csrf_protector_logger;

            if( $canReturnJSON ) {
                csrfProtector::setErrorHandler( new FileSendercsrfProtectorErrorHandler($canReturnJSON));
            }
            csrfProtector::init($length,$action,$logger);
        }
    }

    /**
     * Get the CSRF token to send to the server
     */
    public static function getCSRFToken() {
        if( Config::get('owasp_csrf_protector_enabled')) {
            return CSRFP._getAuthKey();
        }
        return '';
    }
}
