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
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 * Time epochs used in the statistics system. These are ranges of time like hour, day etc
 * that can be used in graphs to select data from the database.
 */
class DBConstantEpochType extends DBConstant
{
    public static function createObject()
    {
        return new self();
    }

    const NARROWEST_TYPE = 'fifteen_minutes';
    const FIFTEEN_MINUTES = 'fifteen_minutes';
    const HOUR  = 'hour';
    const DAY   = 'day';
    const WEEK  = 'week';
    const MONTH = 'month';
    const YEAR  = 'year';
    
    protected function getEnum()
    {
        return array(
            self::FIFTEEN_MINUTES => 1,
            self::HOUR  => 2,
            self::DAY   => 3,
            self::WEEK  => 4,
            self::MONTH => 5,
            self::YEAR  => 6,
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
    
    static function broaden( $v ) {
        if( $v == self::FIFTEEN_MINUTES ) {
            return self::HOUR;
        }
        if( $v == self::HOUR ) {
            return self::DAY;
        }
        if( $v == self::DAY ) {
            return self::WEEK;
        }
        if( $v == self::WEEK ) {
            return self::MONTH;
        }
        if( $v == self::MONTH ) {
            return self::YEAR;
        }
        if( $v == self::YEAR ) {
            return null;
        }
    }
    
}
