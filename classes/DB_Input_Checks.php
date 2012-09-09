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

/*  --------------------------------
 * This Class takes care of the input checks required in sql queries.
 * Each check return the value or the report the error in errorReporting.
 * All the Vars will go through a mysqlEscape function, in this funtion different ways of general string filtering can be used.
 */

/*
 * To make the errorReporting complete this file needs another file that defines the error messages.
 * They can be build in here by a switch, but its better todo this in another file so the user/developer can easly adjust the message.
 *  --------------------------------
 */

// date check 

class DB_Input_Checks {

    /*
     * Public Functions
     */
    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 

    public function checkEmail($email) {

        if (preg_match(';^([a-z0-9_-]+)(.[a-z0-9_-]+)*@([a-z0-9-]+)(.[a-z0-9-]+)*.[a-z]{2,4}$;i', $email)) {
            //if (preg_match("[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?", $email)) {

            return $email;
        } else {
            $this->errorReport('email'); 
        }
    }

    public function checkURL($url) {

        if (preg_match(';^http\:\/\/[a-z0-9-]+.([a-z0-9-]+.)?[a-z]+;i', $url)) {
            return $url;
        } else {
            $this->errorReport('url'); 
        }
    }


    public function checkIp($ip) {
        // Creates a long2ip string and then creates and IP from that string. So if the string is invalide the ip will the remade to a normal ip.
        return long2ip(ip2long($ip));
    }

    public function checkIp6($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }
        return "::";
    }

    /*
     * Private Functions
     */

    private function errorReport($errorMessage) {
        // errorReport will handle the default error messaging. 

        //Todo: Fix proper error handleing with proper message towards the Flex frontend.
    }

}

