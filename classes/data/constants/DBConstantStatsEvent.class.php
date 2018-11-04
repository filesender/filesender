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
 * These are events that have happened that might be of interest
 * resume is recorded so that we can see how many times users have
 * been having issues uploading.
 */
class DBConstantStatsEvent extends DBConstant
{
    public static function createObject()
    {
        return new self();
    }

    const UPLOAD_STARTED  = LogEventTypes::UPLOAD_STARTED;
    const UPLOAD_RESUMED  = LogEventTypes::UPLOAD_RESUMED;
    const UPLOAD_ENDED    = LogEventTypes::UPLOAD_ENDED;
    const USER_CREATED    = LogEventTypes::USER_CREATED;
    const USER_PURGED     = LogEventTypes::USER_PURGED;
    const USER_INACTIVED  = LogEventTypes::USER_INACTIVED;
    const DOWNLOAD_STARTED = LogEventTypes::DOWNLOAD_STARTED;
    const DOWNLOAD_RESUMED = LogEventTypes::DOWNLOAD_RESUMED;
    const DOWNLOAD_ENDED = LogEventTypes::DOWNLOAD_ENDED;
    
    protected function getEnum()
    {
        return array(
            self::UPLOAD_STARTED  => 1,
            self::UPLOAD_RESUMED  => 2,
            self::UPLOAD_ENDED    => 3,
            self::USER_CREATED    => 4,
            self::USER_PURGED     => 5,
            self::USER_INACTIVED  => 6,
            self::DOWNLOAD_STARTED  => 7,
            self::DOWNLOAD_RESUMED  => 8,
            self::DOWNLOAD_ENDED    => 9,
        );
    }

    static function validateCGIParamOrDIE( $v )
    {
        if (!preg_match('`^[a-z_-]{1,40}$`', $v)) {
            Logger::haltWithErorr('invalid db contant stats event given '.$v);
        }
        self::lookup($v);
        return $v;
    }

    static function fromLogEventType( $t )
    {
        if( $t == LogEventTypes::UPLOAD_STARTED ) {
            return self::lookup(self::UPLOAD_STARTED);
        }
        if( $t == LogEventTypes::UPLOAD_RESUMED ) {
            return self::lookup(self::UPLOAD_RESUMED);
        }
        if( $t == LogEventTypes::UPLOAD_ENDED ) {
            return self::lookup(self::UPLOAD_ENDED);
        }
        if( $t == LogEventTypes::USER_CREATED ) {
            return self::lookup(self::USER_CREATED);
        }
        if( $t == LogEventTypes::USER_INACTIVED ) {
            return self::lookup(self::USER_INACTIVED);
        }
        if( $t == LogEventTypes::USER_PURGED ) {
            return self::lookup(self::USER_PURGED);
        }
        if( $t == LogEventTypes::DOWNLOAD_STARTED ) {
            return self::lookup(self::DOWNLOAD_STARTED);
        }
        if( $t == LogEventTypes::DOWNLOAD_RESUMED ) {
            return self::lookup(self::DOWNLOAD_RESUMED);
        }
        if( $t == LogEventTypes::DOWNLOAD_ENDED ) {
            return self::lookup(self::DOWNLOAD_ENDED);
        }
        return null;
    }
}
