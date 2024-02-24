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

if (!defined('FILESENDER_BASE')) {
    die('autoload.php was called without init.php.');
}

/**
 * Autoloading helper
 */
class Autoloader
{
    /**
     * Class name to path mappers
     */
    private static $mappers = array(
        'PropertyAccessException' => 'exceptions/DBObjectExceptions',
        '*Exception' => 'exceptions/@package(Exception)',
        
        // CONSTANTS
        '*Types' => 'constants/',
        '*Options' => 'constants/',
        '*Constants' => 'constants/',
        '*Statuses' => 'constants/',
        'GUIPages' => 'constants/',
        'LogLevels' => 'constants/',
        'ReportFormats' => 'constants/',
        'DatabaseSchemaVersions' => 'constants/',
        
        'Storage' => 'storage/',
        'Storage*' => 'storage/',

        // WARNING
        // WARNING, the migration script uses things in data in a special way
        // WARNING
        'DBObject' => 'data/',
        'CollectionType' => 'data/',
        'FileCollection' => 'data/',
        'Collection' => 'data/',
        'CollectionDirectory' => 'data/',
        'CollectionTree' => 'data/',
        'Transfer' => 'data/',
        'File' => 'data/',
        'ShredFile' => 'data/',
        'Recipient' => 'data/',
        'Guest' => 'data/',
        'User' => 'data/',
        'TrackingEvent' => 'data/',
        'TranslatableEmail' => 'data/',
        'Metadata' => 'data/',
        'Authentication' => 'data/',
        '*Log' => 'data/',
        'DBTestingTable*' => 'data/',
        'AggregateStatistic' => 'data/',
        'AggregateStatisticMetadata' => 'data/',
        'AVResult' => 'data/',
        'DownloadOneTimePassword' => 'data/',
        'RateLimitHistory' => 'data/',
        
        'AVProgram*' => 'avprograms/',
        

        // In database constants
        'DBConstant*' => 'data/constants/',
        

        'DBLayer' => 'utils/',

        'Auth*' => 'auth/',
        
        'RestEndpoint' => 'rest/',
        'RestEndpoint*' => 'rest/endpoints/',
        'Rest*' => 'rest/',
        
        'Mail*' => 'utils/Mail',
        

        'TestDatabase*' => 'test/database/',


        // Must be last
        '*' => 'utils/'
    );
    
    /**
     * Load a class
     */
    public static function load($class)
    {
        foreach (self::$mappers as $matcher => $path) {
            $m = uniqid();
            $matcher = str_replace('*', $m, $matcher);
            $matcher = preg_quote($matcher);
            $matcher = str_replace($m, '.*', $matcher);
            $matcher = '`^'.$matcher.'$`';
            
            if (preg_match($matcher, $class)) {
                if (preg_match('`^(.*)@package\((.+)\)$`', $path, $m)) {
                    $path = self::package($m[1], $class, $m[2]);
                }
                
                $file = FILESENDER_BASE.'/classes/'.$path;
                if (!$path || substr($path, -1) == '/') {
                    $file .= $class;
                }
                $file .= '.class.php';
                
                if (!file_exists($file)) {
                    Logger::debug('Looking for class '.$class.', expecting it at '.$file.' but nothing found, may (or may not) be a problem ...');
                    return;
                }
                
                require_once $file;
                
                return;
            }
        }
        
        Logger::debug('Could not find class '.$class.', may (or may not) be a problem ...');
    }
    
    /**
     * Resolve tokenized package
     *
     * @param $path string the search path
     * @param $class string the class name
     * @param $type string the default class
     *
     * @return string the class path
     */
    private static function package($path, $class, $type)
    {
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        
        $tokens = array();
        $bits = preg_split('`([A-Z][a-z0-9]*)`', $class, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        while ($bit = array_shift($bits)) {
            if (preg_match('`^[A-Z]$`', $bit)) {
                while (count($bits) && preg_match('`^[A-Z]$`', $bits[0])) {
                    $bit .= array_shift($bits);
                }
            }
            
            $tokens[] = $bit;
        }
        
        array_pop($tokens); // Exception
        
        while (count($tokens) && !file_exists(FILESENDER_BASE.'/classes/'.$path.implode('', $tokens).$type.'s.class.php')) {
            array_pop($tokens);
        }
        
        return $path.(count($tokens) ? implode('', $tokens) : '').$type.'s';
    }
}

/**
 * Register autoload
 */
spl_autoload_register(function ($class) {
    Autoloader::load($class);
});
