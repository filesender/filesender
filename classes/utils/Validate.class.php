<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * Data validation
 */
class Validate
{
    /**
     * Log a message about a property that has failed filter_var.
     * 
     * This is a function to allow outside code to also log failures
     * in the same way.
     */ 
    public static function filter_var_log( $msg )
    {
        Logger::info("Validate FAILING. REST data filtering failing for $msg");
    }
    
    /**
     * This will only accept 1 or true as being true, any other input
     * is assumed to be false. The $msg is included as things may be
     * expanded to check for false,0 values and log for out of range
     * input.
     */
    public static function filter_var_bool( $msg, $value )
    {
        $ret = Utilities::isTrue( $value );
        return $ret;
    }

    public static function filter_var_number( $msg, $value )
    {
        $ret = self::filter_var_regex_log(
            $msg,
            $value,
            "|^[0-9]{1,32}$|" );
        return $ret;
    }


    public static function filter_var_token( $msg, $value )
    {
        $ret = self::filter_var_regex_log(
            $msg,
            $value,
            "|^[-a-z0-9]{1,40}$|" );
        return $ret;
    }
    
    
    /**
     * Similar to filter_var but a message ($msg) is logged if
     * validation does not return the same value as was passed.
     */
    public static function filter_var_regex_log( $msg, $value, $r )
    {
        if( $value === null ) {
            return $value;
        }
        $value = rtrim($value,"\n\r");
        $filter = FILTER_VALIDATE_REGEXP;
        $original = $value;
        $ret = filter_var( $value, $filter,
                           ["options" => ["regexp" => $r ]] );
        if( $ret != $value ) {
            self::filter_var_log($msg);
        }
        return $ret;
    }

    public static function filter_var_mimetype( $msg, $value )
    {
        if( $value === null ) {
            return $value;
        }
        
        // trim off (obj) or +ext and other trailing stuff
        $value = preg_replace('/[+ (].*$/','',$value );

        return self::filter_var_regex_log( $msg,
                                           $value,
                                           '|^[.a-zA-Z0-9]+/[.a-zA-Z0-9]+$|' );
            
    }

    public static function filter_var_email( $msg, $value )
    {
        if(!Utilities::validateEmail($value)) {
            self::filter_var_log($msg);
            return "";
        }
        return $value;
    }

    public static function filter_var_lang( $msg, $value )
    {
        $ret = Validate::filter_var_regex_log(
            $msg, $value,
            '/^[-a-z]{1,12}$/'  );
        return $ret;
    }
    
}
