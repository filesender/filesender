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
if(!defined('FILESENDER_BASE')) die('Missing environment');

/**
 * Some handy Date/Time and other functions that are not the 
 * same on all backends. This is useful for abstraction and keeping
 * the logic to do that in a single place. If something is slightly
 * different between database backends then it should be handled here.
 */
class DBLayer {

    public static function isMySQL() {
        return Config::get('db_type') == "mysql";
    }
    public static function isPostgress() {
        return Config::get('db_type') == "pgsql";
    }

    /**
     * 
     * @return string SQL fragment
     */
    public static function toIntervalDays( $exp ) {
        if(self::isPostgress()) {
            return "(($exp) || ' DAYS')::INTERVAL";
        }
        if(self::isMySQL()) {
            return "INTERVAL $exp DAY";
        }
        throw new DBIBackendExplicitHandlerUnimplementedException(
            'SQLUNIMP toIntervalDays() called on unsupported backend');
    }

    /**
     * number of days between timestamp f1 and f2 
     * 
     * @return string SQL fragment
     */
    public static function datediff( $f1, $f2 ) {
        if(self::isPostgress()) {
            return "extract(day from " . $f1 . "-" . $f2 . " )";
        }
        if(self::isMySQL()) {
            return "DATEDIFF(" . $f1 . "," . $f2 . ")";
        }
        throw new DBIBackendExplicitHandlerUnimplementedException(
            'SQLUNIMP datediff() called on unsupported backend');
    }
    
    /**
     * Given an SQL timestamp field $f convert that value to the 
     * number of seconds since unix epoch
     * 
     * @return string SQL fragment
     */
    public static function timeStampToEpoch( $f ){
        if(self::isPostgress()) {
            return "extract(epoch from " . $f . ")";
        }
        if(self::isMySQL()) {
            return "UNIX_TIMESTAMP(" . $f .")";
        }
        throw new DBIBackendExplicitHandlerUnimplementedException(
            'SQLUNIMP timeStampToEpoch() called on unsupported backend');
    }
    
}

