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
        
        $rnd = self::generateRandomHexString();
        
        return substr($rnd, 0, 8).'-'.substr($rnd, 8, 4).'-'.substr($rnd, 12, 4).'-'.substr($rnd, 16, 4).'-'.substr($rnd, 20, 12);
    }
    
    /**
     * Validates unique ID format
     * 
     * @param string $uid
     * 
     * @return bool
     */
    public static function isValidUID($uid) {
        return preg_match('/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/i', $uid);
    }
    
    /**
     * Generate (pseudo) random hex string
     * 
     * @return string
     */
    public static function generateRandomHexString($nearly = false) {
        $len = mt_rand(16, 32);
        
        $rnd = '';
        for($i=0; $i<$len; $i++) $rnd .= sprintf('%04d', mt_rand(0, 9999));
        
        if($nearly) return hash('sha1', $rnd);
        
        $sfile = FILESENDER_BASE.'/tmp/instance.secret';
        if(file_exists($sfile)) {
            $ctn = array_filter(array_map('trim', explode("\n", file_get_contents($sfile))), function($line) {
                return substr($line, 0, 1) != '#';
            });
            
            $secret = array_shift($ctn);
        } else {
            $secret = self::generateRandomHexString(true);
            
            if($fh = fopen($sfile, 'w')) {
                fwrite($fh, '# Automatically generated'."\n");
                fwrite($fh, $secret);
                fclose($fh);
            } else throw new CoreCannotWriteFileException($sfile);
        }
        
        return hash_hmac('sha1', $rnd, $secret);
    }
    
    /**
     * Get instance uid
     * 
     * @return string
     */
    public static function instanceUID() {
        $sfile = FILESENDER_BASE.'/tmp/instance.uid';
        if(file_exists($sfile)) {
            $ctn = array_filter(array_map('trim', explode("\n", file_get_contents($sfile))), function($line) {
                return substr($line, 0, 1) != '#';
            });
            
            $uid = array_shift($ctn);
        } else {
            $uid = self::generateRandomHexString(true);
            
            if($fh = fopen($sfile, 'w')) {
                fwrite($fh, '# Automatically generated'."\n");
                fwrite($fh, $uid);
                fclose($fh);
            } else throw new CoreCannotWriteFileException($sfile);
        }
        
        return $uid;
    }
    
    /**
     * Get running instance uid
     * 
     * @return string
     */
    public static function runningInstanceUID() {
        return substr(self::instanceUID(), -8).'-'.filemtime(FILESENDER_BASE.'/config/config.php');
    }
    
    /**
     * Format a date according to configuration
     * 
     * @param integer $timestamp php timestamp to format to date or null to use current date
     * @param bool $with_time
     * 
     * @return string formatted date
     */
    public static function formatDate($timestamp = null, $with_time = false) {
        $lid = $with_time ? 'datetime_format' : 'date_format';
        $dateFormat = Lang::tr($lid);
        if ($dateFormat == '{date_format}')
            $dateFormat = 'Y-m-d';
        if ($dateFormat == '{datetime_format}')
            $dateFormat = 'Y-m-d H:i:s';
        
        return date($dateFormat, is_null($timestamp) ? time() : $timestamp);
    }
    
    /**
     * Format a time according to configuration
     * 
     * @param integer $time in seconds
     * 
     * @return string formatted time
     */
    public static function formatTime($time) {
        $time_format = Lang::tr('time_format');
        if ($time_format == '{time_format}')
            $time_format = '{h:H\h} {i:i\m\i\n} {s:s\s}';
        
        $bits = array();
        
        $bits['h'] = floor($time / 3600);
        $time %= 3600;
        $bits['i'] = floor($time / 60);
        $bits['s'] = $time % 60;
        
        foreach($bits as $k => $v) {
            if($v) {
                $time_format = preg_replace_callback('`\{'.$k.':([^}]+)\}`', function($m) use($k, $v) {
                    return preg_replace_callback('`(?<!\\\\)'.$k.'`i', function($m) use($v) {
                        return sprintf('%02d', $v);
                    }, $m[1]);
                }, $time_format);
            } else { // Remove part if zero
                $time_format = preg_replace('`\{'.$k.':[^}]+\}`', '', $time_format);
            }
        }
        
        // Remove backslashes
        $time_format = str_replace('\\', '', $time_format);
        
        // Strip leading 0s
        $time_format = preg_replace('`^[\s0]+`', '', $time_format);
        
        return trim($time_format);
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
        if (!preg_match('`^([0-9]+)([ptgmk])?$`i', trim($size), $parts))
            throw new BadSizeFormatException($size);
        
        $size = (int)$parts[1];
        
        if(count($parts) > 2) switch(strtoupper($parts[2])) {
            case 'P': $size *= 1024;
            case 'T': $size *= 1024;
            case 'G': $size *= 1024;
            case 'M': $size *= 1024;
            case 'K': $size *= 1024;
        }
        return $size;
    }
    
    /**
     * Format size
     * 
     * @param int $bytes
     * 
     * @return string
     */
    public static function formatBytes($bytes, $precision = 1) {
        if(!$precision || !is_numeric($precision))
            $precision = 2;
        
        $nomult = Lang::tr('bytes_no_multiplier')->out();
        if($nomult == '{bytes_no_multiplier}') $nomult = 'Bytes';
        
        $wmult = Lang::tr('bytes_with_multiplier')->out();
        if($wmult == '{bytes_with_multiplier}') $wmult = 'B';
        
        $multipliers = array('', 'k', 'M', 'G', 'T');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($multipliers) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision).' '.$multipliers[$pow].($pow ? $wmult : $nomult);
    }
    
    /**
     * Get remote client IP (v4 or v6)
     * 
     * @return string
     */
    public static function getClientIP(){
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ErrorTypes::NO_IP;
    }
    
    /**
     * Replace illegal chars with _ character in supplied file names
     * 
     * @param string $filename
     * 
     * @return string
     */
    public static function sanitizeFilename($filename) {
        //return preg_replace('`[^a-z0-9_\-\. ]`i', '_', $filename);
        return preg_replace('`^\.`', '_', (string)$filename);
    }
    
    /**
     * Sanitize input against encoding variations and (a bit) against html injection
     * 
     * @param mixed $input
     * 
     * @return mixed
     */
    public static function sanitizeInput($input) {
        if(is_array($input)) {
            foreach($input as $k => $v) {
                $nk = preg_replace('`[^a-z0-9\._-]`', '', $k);
                if($k !== $nk) unset($input[$k]);
                $input[$nk] = self::sanitizeInput($v);
            }
            
            return $input;
        }
        
        if(
            is_numeric($input)
            || is_bool($input)
            || is_null($input)
            || is_object($input) // How can that be ?
        )
            return $input;
        
        if(is_string($input)) {
            // Convert to UTF-8
            $input = iconv(mb_detect_encoding($input, mb_detect_order(), true), 'UTF-8', $input);
            
            // Render potential tags useless by putting a space immediatelly after < which does not already have one
            $input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
            $input = preg_replace('`<([^\s])`', '< $1', $input);
            
            return $input;
        }
        
        // Still here ? Should not ...
        return null;
    }
    
    /**
     * Sanitize output
     * 
     * @param string $output
     * 
     * @return string
     */
    public static function sanitizeOutput($output) {
        return htmlentities($output, ENT_QUOTES, 'UTF-8');
    }
}

