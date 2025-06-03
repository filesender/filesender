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
    const UPLOAD_MAXSIZE_ENDED = 'upload_maxsize_ended';
    const DOWNLOAD_MAXSIZE_ENDED = 'upload_maxsize_ended';

    const UPLOAD_NOCRYPT_STARTED   = 'upload_nocrypt_started';
    const UPLOAD_NOCRYPT_RESUMED   = 'upload_nocrypt_resumed';
    const UPLOAD_NOCRYPT_ENDED     = 'upload_nocrypt_ended';
    const DOWNLOAD_NOCRYPT_STARTED = 'download_nocrypt_started';
    const DOWNLOAD_NOCRYPT_RESUMED = 'download_nocrypt_resumed';
    const DOWNLOAD_NOCRYPT_ENDED   = 'download_nocrypt_ended';
    const UPLOAD_NOCRYPT_MAXSIZE_ENDED = 'upload_nocrypt_maxsize_ended';
    const DOWNLOAD_NOCRYPT_MAXSIZE_ENDED = 'upload_nocrypt_maxsize_ended';

    const UPLOAD_ENCRYPTED_STARTED   = 'upload_encrypted_started';
    const UPLOAD_ENCRYPTED_RESUMED   = 'upload_encrypted_resumed';
    const UPLOAD_ENCRYPTED_ENDED     = 'upload_encrypted_ended';
    const DOWNLOAD_ENCRYPTED_STARTED = 'download_encrypted_started';
    const DOWNLOAD_ENCRYPTED_RESUMED = 'download_encrypted_resumed';
    const DOWNLOAD_ENCRYPTED_ENDED   = 'download_encrypted_ended';
    const UPLOAD_ENCRYPTED_MAXSIZE_ENDED = 'upload_encrypted_maxsize_ended';
    const DOWNLOAD_ENCRYPTED_MAXSIZE_ENDED = 'upload_encrypted_maxsize_ended';

    const OFFSET_NOCRYPT   = 100;
    const OFFSET_ENCRYPTED = 200;

    const STORAGE_EXPIRED_TRANSFERS_SIZE = 'storage-expired-transfers-size';
    const STORAGE_USED_SIZE = 'storage-used-size';
    const STORAGE_FREE_SIZE = 'storage-free-size';
    const OFFSET_STORAGE = 500;
    const OFFSET_STORAGE_END = 510;
    
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
            self::UPLOAD_MAXSIZE_ENDED    => 10,
            self::DOWNLOAD_MAXSIZE_ENDED  => 11,
 
            // It is important that these are the same as the above upload and download
            // started, resumed, and ended but with an offset.
            self::UPLOAD_NOCRYPT_STARTED    => self::OFFSET_NOCRYPT + 1,
            self::UPLOAD_NOCRYPT_RESUMED    => self::OFFSET_NOCRYPT + 2,
            self::UPLOAD_NOCRYPT_ENDED      => self::OFFSET_NOCRYPT + 3,
            self::DOWNLOAD_NOCRYPT_STARTED  => self::OFFSET_NOCRYPT + 7,
            self::DOWNLOAD_NOCRYPT_RESUMED  => self::OFFSET_NOCRYPT + 8,
            self::DOWNLOAD_NOCRYPT_ENDED    => self::OFFSET_NOCRYPT + 9,
            self::UPLOAD_NOCRYPT_MAXSIZE_ENDED    => self::OFFSET_NOCRYPT + 10,
            self::DOWNLOAD_NOCRYPT_MAXSIZE_ENDED  => self::OFFSET_NOCRYPT + 11,

            self::UPLOAD_ENCRYPTED_STARTED    => self::OFFSET_ENCRYPTED + 1,
            self::UPLOAD_ENCRYPTED_RESUMED    => self::OFFSET_ENCRYPTED + 2,
            self::UPLOAD_ENCRYPTED_ENDED      => self::OFFSET_ENCRYPTED + 3,
            self::DOWNLOAD_ENCRYPTED_STARTED  => self::OFFSET_ENCRYPTED + 7,
            self::DOWNLOAD_ENCRYPTED_RESUMED  => self::OFFSET_ENCRYPTED + 8,
            self::DOWNLOAD_ENCRYPTED_ENDED    => self::OFFSET_ENCRYPTED + 9,
            self::UPLOAD_ENCRYPTED_MAXSIZE_ENDED    => self::OFFSET_ENCRYPTED + 10,
            self::DOWNLOAD_ENCRYPTED_MAXSIZE_ENDED  => self::OFFSET_ENCRYPTED + 11,

            self::STORAGE_EXPIRED_TRANSFERS_SIZE => self::OFFSET_STORAGE + 1,
            self::STORAGE_USED_SIZE => self::OFFSET_STORAGE + 2,
            self::STORAGE_FREE_SIZE => self::OFFSET_STORAGE + 3,
            
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
    static function augmentToEventConsideringEncryption( $ev, DBObject $target )
    {
        Logger::debug("augmentToEventConsideringEncryption() testing ev " . $ev . " class: " . get_class($target) );
        if( $ev >= self::lookup(self::UPLOAD_STARTED) && $ev <= self::lookup(self::DOWNLOAD_ENDED) )
        {
            if( $ev >= self::lookup(self::USER_CREATED) && $ev <= self::lookup(self::USER_INACTIVED) ) {
                return $ev;
            }

            $transfer = NULL;
            switch (get_class($target)) {
                case Transfer::getClassName():
                    $transfer = $target;
                    break;
                case File::getClassName():
                    $transfer = $target->transfer;
                    break;
            }

            if( $transfer ) {
                if( $transfer->is_encrypted ) {
                    return $ev + self::OFFSET_ENCRYPTED;
                }
                return $ev + self::OFFSET_NOCRYPT;
            }
        }
        return $ev;
    }
}
