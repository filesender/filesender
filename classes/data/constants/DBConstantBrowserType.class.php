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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 */
class DBConstantBrowserType extends DBConstant
{
    public static function createObject()
    {
        return new self();
    }

    const T_UNKNOWN = 'Unknown';
    const T_EDGE    = 'Edge';
    const T_IE      = 'Internet Explorer';
    const T_FIREFOX = 'Mozilla Firefox';
    const T_VIVALDI = 'Vivaldi';
    const T_OPERA   = 'Opera';
    const T_CHROME  = 'Google Chrome';
    const T_SAFARI  = 'Apple Safari';
    const T_OUTLOOK = 'Outlook';
    
    protected function getEnum()
    {
        return array(
            self::T_UNKNOWN  => 0,
            self::T_EDGE     => 1,
            self::T_IE       => 2,
            self::T_FIREFOX  => 3,
            self::T_VIVALDI  => 4,
            self::T_OPERA    => 5,
            self::T_CHROME   => 6,
            self::T_SAFARI   => 7,
            self::T_OUTLOOK  => 8,
        );
    }

    /////////////////////////////
    //
    //

    static function validateCGIParamOrDIE( $v )
    {
        if (!preg_match('`^[a-z_]{1,40}$`', $v)) {
            exit(1);
        }
        self::lookup($v);
        return $v;
    }

    static function currentBrowserToEnum()
    {
        if(!isset($_SERVER['HTTP_USER_AGENT'])) {
            return self::lookup(self::T_UNKNOWN);
        }
        $b = $_SERVER['HTTP_USER_AGENT'];
        if( preg_match( '/Firefox/i', $b )) {
            $v = self::T_FIREFOX;
        } elseif ( preg_match( '/edge/i', $b )) {
            $v = self::T_EDGE;
        } elseif ( preg_match( '/MSIE/i', $b )) {
            $v = self::T_IE;
        } elseif ( preg_match( '/Trident/i', $b )) {
            $v = self::T_IE;
        } elseif ( preg_match( '/Vivaldi/i', $b )) {
            $v = self::T_VIVALDI;
        } elseif ( preg_match( '/Opera/i', $b )) {
            $v = self::T_OPERA;
        } elseif ( preg_match( '/OPR/i', $b )) {
            $v = self::T_OPERA;
        } elseif ( preg_match( '/Chrome/i', $b )) {
            $v = self::T_CHROME;
        } elseif ( preg_match( '/CriOS/i', $b )) {
            $v = self::T_CHROME;
        } elseif ( preg_match( '/Safari/i', $b )) {
            $v = self::T_SAFARI;
        } elseif ( preg_match( '/Outlook/i', $b )) {
            $v = self::T_OUTLOOK;
        } else {
            $v = self::T_UNKNOWN;
        }
        return self::lookup($v);
    }
    
    
}
