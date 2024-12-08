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
class DBConstantOperatingSystem extends DBConstant
{
    public static function createObject()
    {
        return new self();
    }

    
    const T_UNKNOWN  = 'Unknown';
    const T_IPAD     = 'iPad';
    const T_IPOD     = 'iPod';
    const T_IPHONE   = 'iPhone';
    const T_MAC      = 'Mac';
    const T_OSX      = 'OSX';
    const T_ANDROID  = 'Android';
    const T_LINUX    = 'Linux';
    const T_NOKIA    = 'Nokia';
    const T_WIN10    = 'Windows 10';
    const T_WIN81    = 'Windows 8.1';
    const T_WIN80    = 'Windows 8.0';
    const T_WIN70    = 'Windows 7.0';
    const T_WINOTHER = 'Windows (Other)';
    const T_FREEBSD  = 'FreeBSD';
    const T_OPENBSD  = 'OpenBSD';
    const T_NETBSD   = 'NetBSD';
    const T_SOLARIS  = 'Solaris';
    const T_SUNOS    = 'SunOS';
    const T_OS2      = 'OS2';
    const T_BEOS     = 'BEOS';

    protected function getEnum()
    {
        return array(
            self::T_UNKNOWN  => 0,
            self::T_IPAD     => 1,
            self::T_IPOD     => 2,
            self::T_IPHONE   => 3,
            self::T_MAC      => 4,
            self::T_OSX      => 5,
            self::T_ANDROID  => 6,
            self::T_LINUX    => 7,
            self::T_NOKIA    => 8,
            self::T_WIN10    => 9,
            self::T_WIN81    => 10,
            self::T_WIN80    => 11,
            self::T_WIN70    => 12,
            self::T_WINOTHER => 13,
            self::T_FREEBSD  => 14,
            self::T_OPENBSD  => 15,
            self::T_NETBSD   => 16,
            self::T_SOLARIS  => 17,
            self::T_OS2      => 18,
            self::T_BEOS     => 19,
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

    static function currentUserOperatingSystemEnum()
    {
        if(!isset($_SERVER['HTTP_USER_AGENT'])) {
            return self::lookup(self::T_UNKNOWN);
        }
        $b = $_SERVER['HTTP_USER_AGENT'];
        if( preg_match( '/iPad/i', $b )) {
            $v = self::T_IPAD;
        } elseif ( preg_match( '/iPod/i', $b )) {
            $v = self::T_IPOD;
        } elseif ( preg_match( '/iPhone/i', $b )) {
            $v = self::T_IPHONE;
        } elseif ( preg_match( '/imac/i', $b )) {
            $v = self::T_MAC;
        } elseif ( preg_match( '/Mac.*OS/i', $b )) {
            $v = self::T_OSX;
        } elseif ( preg_match( '/android/i', $b )) {
            $v = self::T_ANDROID;
        } elseif ( preg_match( '/linux/i', $b )) {
            $v = self::T_LINUX;
        } elseif ( preg_match( '/Nokia/i', $b )) {
            $v = self::T_NOKIA;
        } elseif ( preg_match( '/Win/i', $b )) {
            $v = self::T_WINOTHER;
            if ( preg_match( '/NT 10.0/i', $b )) {
                $v = self::T_WIN10;
            } elseif ( preg_match( '/NT 6.3/i', $b )) {
                $v = self::T_WIN81;
            } elseif ( preg_match( '/NT 6.2/i', $b )) {
                $v = self::T_WIN80;
            } elseif ( preg_match( '/NT 6.1/i', $b )) {
                $v = self::T_WIN70;
            }
        } else {
            $v = self::T_UNKNOWN;
        }

        return self::lookup($v);
    }
    
    
}
