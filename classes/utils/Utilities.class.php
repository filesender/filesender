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
     * Validates unique ID format
     * 
     * @param string $uid
     * 
     * @return bool
     */
    public static function isValidUID($uid) {
        return preg_match('/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/', $uid);
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
        $dateFormat = Lang::tr('date_format');
        if ($dateFormat == '{date_format}')
            $dateFormat = 'Y-m-d';
        return date($dateFormat, is_null($timestamp) ? time() : $timestamp);
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
    
    
    // --------------------------------
    // Imported from Functions.php
    // --------------------------------
    
    
    /**
     * Replace illegal chars with _ character in supplied file names.
     * TODO: check where it is more efficient to check the filename 
     * 
     * @param type $fileName
     * @return string
     */
    public static function sanitizeFilename($fileName){
        if (! empty($fileName)) {
            $fileName = preg_replace("/^\./", "_", $fileName); //return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
            return $fileName;
        } else {
            //trigger_error("invalid empty filename", E_USER_ERROR);
            return "";
        }
    }
    
    
    /**
     * Force HTTPS - redirects HTTP to HTTPS.
     */
    public static function forceHTTPS(){
        if (
            // Unless force_ssl is false or 0 ...
            Config::get('force_ssl') !== false && Config::get('force_ssl') !== 0
            // ... or we're on https ...
            && !(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        ) {
            if (session_id() != '') {
                // Destroy current session to prevent stealing session, because someone may have sniffed it during our HTTP (not HTTPS) request.
                unset($_SESSION);

                if (ini_get('session.use_cookies')) {
                    // Unset the PHPSESSID cookie, so that the user will get a new session ID on their next request.
                    $params = session_get_cookie_params();

                    setcookie(
                        session_name(), '', time() - 42000,
                        $params['path'], $params['domain'],
                        $params['secure'], $params['httponly']
                    );
                }

                session_destroy();
            }

            // ... Redirect the user to HTTPS.
            $redirect = sprintf('Location: https://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
            header($redirect);
            exit;
        }
    }
}

