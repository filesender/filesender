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
if (!defined('FILESENDER_BASE'))
    die('Missing environment');

/**
 * Utility functions holder
 */
class Utilities 
{
    /**
     * Generate a unique ID to be used as token
     * 
     * @param callable $unicity_checker callback used to check for uid unicity (takes uid as sole argument, returns bool telling if uid is unique), null if check not needed
     * @param int $max_tries maximum number of tries before giving up and throwing
     * 
     * @return string uid
     * 
     * @throws UtilitiesUidGeneratorBadUnicityCheckerException
     * @throws UtilitiesUidGeneratorTriedTooMuchException
     */
    public static function generateUID($unicity_checker = null, $max_tries = 1000) {
        if($unicity_checker) {
            if(!is_callable($unicity_checker))
                throw new UtilitiesUidGeneratorBadUnicityCheckerException();
            
            $tries = 0;
            do {
                $uid = self::generateUID();
                $tries++;
            } while(!call_user_func($unicity_checker, $uid) && ($tries <= $max_tries));
            
            if($tries > $max_tries)
                throw new UtilitiesUidGeneratorTriedTooMuchException($tries);
            
            return $uid;
        }
        
        return sprintf(
            '%08x-%04x-%04x-%02x%02x-%012x',
            mt_rand(),
            mt_rand(0, 65535),
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)),
            bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
            mt_rand(0, 255),
            mt_rand()
        );
    }
    
    /**
     * Format a date according to configuration
     * 
     * @param integer $timestamp php timestamp to format to date or null to use current date
     * 
     * @return string formatted date
     */
    public static function formatDate($timestamp = null)
    {
        return date(Config::get('datedisplayformat'), is_null($timestamp) ? time() : $timestamp);
    }
    
    /**
     * Turn PHP defined size (ini files) to bytes
     * 
     * @param string $size the size to analyse
     * 
     * @return integer the size in bytes
     */
    public static function sizeToBytes($size)
    {
        if (!preg_match('`^([0-9]+)([ptgmk])$`i', trim($size), $parts))
            throw new BadSizeFormatException($size);
        
        $size = (int)$parts[1];
        
        switch(strtoupper($parts[2])) {
            case 'P': $size *= 1024;
            case 'T': $size *= 1024;
            case 'G': $size *= 1024;
            case 'M': $size *= 1024;
            case 'K': $size *= 1024;
        }
        return $size;
    }
    
    
    public static function getClientIP(){
        return isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:Errors::NO_IP;
    }
}

