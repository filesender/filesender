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

// --------------------------------
// Functions for the input checks requires in SQL queries.
// --------------------------------
class DB_Input_Checks
{
    private static $instance = null;

    public static function getInstance()
    {
        // Check for both equality and type.
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function checkEmail($email)
    {
        if (preg_match(';^([a-z0-9_-]+)(.[a-z0-9_-]+)*@([a-z0-9-]+)(.[a-z0-9-]+)*.[a-z]{2,4}$;i', $email)) {
            return $email;
        } else {
            $this->errorReport('email');
        }

        return false;
    }


    private function errorReport($errorMessage)
    {
        // TODO: ...something?
    }

    public function checkURL($url)
    {
        if (preg_match(';^http\:\/\/[a-z0-9-]+.([a-z0-9-]+.)?[a-z]+;i', $url)) {
            return $url;
        } else {
            $this->errorReport('url');
        }

        return false;
    }

    // --------------------------------
    // Creates a long2ip string and then creates an IP from that string.
    // If the string is invalid, the IP is remade to a normal IP.
    // --------------------------------
    public function checkIP($ip)
    {
        return long2ip(ip2long($ip));
    }

    public function checkIPv6($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }

        return "::";
    }
}

