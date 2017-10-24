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
 * Log utility
 */
class Logger {
    
    
    /**
     * Log levels to log priorities conversion table
     */
    private static $levels = array(
        LogLevels::ERROR    => 0,
        LogLevels::WARN     => 1,
        LogLevels::INFO     => 2,
        LogLevels::DEBUG    => 3
    );
    
    
    /**
     * Logging facilities
     */
    private static $facilities = null;
    
    /**
     * Current process
     */
    private static $process = ProcessTypes::MISC;
    
    /**
     * Set current process
     * 
     * @param string $process process name
     */
    public static function setProcess($process) {
        if (!ProcessTypes::isValidValue($process)) $process = ProcessTypes::MISC; 
        self::$process = $process;
    }

    /**
     * True if we are a local process such as ProcessTypes::CRON
     * for which SAML is unlikely to work
     */
    public static function isLocalProcess() {
        return in_array( self::$process,
                         array( ProcessTypes::CRON,
                                ProcessTypes::FEEDBACK,
                                ProcessTypes::INSTALL,
                                ProcessTypes::UPGRADE ));
    }
    
    /**
     * Setup logging facilities
     */
    private static function setup() {
        if(!is_null(self::$facilities)) return;

        self::$facilities = array(array('type' => 'error_log', 'method' => 'logErrorLog')); // Failsafe facility so we have at least one if no valid ones defined in config
        
        // Get facilities from config, cast to single type
        $facilities = Config::get('log_facilities');
        if(!$facilities) $facilities = array();
        if(!is_array($facilities)) $facilities = array('type' => $facilities);
        if(!is_numeric(key($facilities))) $facilities = array($facilities);
        
        // Lookup valid facilities
        foreach($facilities as $index => $facility) {
            
            // Casting and facility defaults
            if(!is_array($facility)) $facility = array('type' => $facility);
            
            if(!array_key_exists('type', $facility))
                throw new ConfigMissingParameterException('log_facilities['.$index.'][type]');

            if (!isset($facility['level']) || !LogLevels::isValidValue($facility['level'])){
                $facility['level'] = LogLevels::INFO;
            }
            
            // Facility type based parameter checks
            switch(strtolower($facility['type'])) {
                case 'file' :
                    // Log to file needs at least a path
                    if(!array_key_exists('path', $facility)) {
                        throw new ConfigMissingParameterException('log_facilities['.$index.'][path]');
                    }
                    
                    // If defined rotation rate must be valid
                    if(array_key_exists('rotate', $facility) && !in_array($facility['rotate'], array('hourly', 'daily', 'weekly', 'monthly', 'yearly')))
                        throw new ConfigBadParameterException('log_facilities['.$index.'][rotate]');
                    
                    $facility['method'] = 'logFile';
                    break;
                    
                case 'syslog' :
                    // PHP syslog arguments may be given
                    $i = false;
                    if(array_key_exists('ident', $facility)) $i = $facility['ident'];
                    
                    $o = 0;
                    if(array_key_exists('option', $facility)) $o = $facility['option'];
                    
                    $f = 0;
                    if(array_key_exists('facility', $facility)) $f = $facility['facility'];
                    
                    // Open syslog backend with given options, report failure if any
                    if($i || $o || $f)
                        if(!openlog($i, $o, $f))
                            throw new ConfigBadParameterException('log_facilities['.$index.']');
                    
                    $facility['method'] = 'logSyslog';
                    break;
                    
                case 'error_log' :
                    // PHP error_log needs no argument
                    $facility['method'] = 'logErrorLog';
                    break;
                    
                case 'callable' :
                    // Callback based facilities need at least a callback ...
                    if(!array_key_exists('callback', $facility))
                        throw new ConfigMissingParameterException('log_facilities['.$index.'][callback]');
                    
                    // ... which must be callable
                    if(!is_callable($facility['callback']))
                        throw new ConfigBadParameterException('log_facilities['.$index.'][callback]');
                    
                    $facility['method'] = 'logCallable';
                    break;
                    
                default :
                    // Unknown facilities are reported
                    throw new ConfigBadParameterException('log_facilities['.$index.'][type]');
            }
            
            self::$facilities[] = $facility;
        }
        
        // Report if no valid facility found (using failsafe)
        if(count($facilities) && count(self::$facilities) < 2)
            throw new ConfigBadParameterException('log_facilities');
        
        // Remove failsafe facility if everything went well
        if(count(self::$facilities) >= 2) array_shift(self::$facilities);
    }
    
    /**
     * Log error
     * 
     * @param string $message
     */
    public static function error($message) {
        self::log(LogLevels::ERROR, $message);
    }
    
    /**
     * Log warn
     * 
     * @param string $message
     */
    public static function warn($message) {
        self::log(LogLevels::WARN, $message);
    }
    
    /**
     * Log info
     * 
     * @param string $message
     */
    public static function info($message) {
        self::log(LogLevels::INFO, $message);
    }
    
    /**
     * Log debug
     * 
     * @param string $message
     */
    public static function debug($message) {
        self::log(LogLevels::DEBUG, $message);
    }
    
    /**
     * Log message
     * 
     * @param string $message
     */
    public static function log($level, $message) {
        // If message is other than scalar (object, array) then print_r it and log individual lines
        if(!is_scalar($message)) {
            foreach(explode("\n", print_r($message, true)) as $line) {
                self::log($level, $line);
            }
            return;
        }
        
        // Setup facilities if not already done
        self::setup();
        
        // Default log level is error so that log calls without level will always be processed
        if(LogLevels::isValidValue($level) && !array_key_exists($level, self::$levels))
            $level = LogLevels::ERROR;
        
        // Add call context data if level is debug
        if($level == LogLevels::DEBUG) {
            // Fecth call stack
            $stack = debug_backtrace();
            
            // Remove Logger internals
            while($stack && array_key_exists('class', $stack[0]) && ($stack[0]['class'] == 'Logger'))
                array_shift($stack);
            
            // If call context is known
            if($stack && array_key_exists('function', $stack[0]) && $stack[0]['function']) {
                $caller = $stack[0];
                
                // Gather code location data
                $s = $caller['file'].':'.$caller['line'].' ';
                if(array_key_exists('class', $caller)) {
                    if(!array_key_exists('type', $caller)) $caller['type'] = ' ';
                    if($caller['type'] == '::') {
                        $s .= $caller['class'].'::';
                    } else $s .= '('.$caller['class'].')'.$caller['type'];
                }
                
                // Resolve magics so that log is easier to read
                if(in_array($caller['function'], array('__call', '__callStatic'))) {
                    $caller['function'] = $caller['args'][0];
                    $caller['args'] = $caller['args'][1];
                }
                
                // Add arguments (objects are mentionned as just "object" without details)
                $args = array();
                foreach($caller['args'] as $arg) {
                    $a = '';
                    if(is_bool($arg)) {
                        $a = $arg ? '(true)' : '(false)';
                    } else if(is_scalar($arg)) {
                        $a = '('.$arg.')';
                    } else if(is_array($arg)) {
                        $a = array();
                        foreach($arg as $k => $v) $a[] = (is_numeric($k) ? '' : $k.' => ').gettype($v).(is_scalar($v) ? (is_bool($v) ? ($v ? '(true)' : '(false)') : '('.$v.')') : '');
                        $a = '('.implode(', ', $a).')';
                    }
                    $args[] = gettype($arg).$a;
                }
                
                $s .= $caller['function'].'('.implode(', ', $args).')';
                
                $message = $s.' '.$message;
            }
        }
        
        // Add authenticated user id if any, except in debug mode as line is already long
        try {
            // No user id in log if we are recording a low level exception
            // as it may end up in throwing another one while getting
            // user / user id and create a loop ...
            $risky_exception = count(array_filter(debug_backtrace(), function($t) {
                return array_key_exists('class', $t) && preg_match('`^(Core|Config|DBI).+Exception$`', $t['class']);
            }));
            
            if($level != LogLevels::DEBUG && !$risky_exception && Auth::user())
                $message = '[user '.Auth::user()->id.'] '.$message;
            
        } catch(Exception $e) {}
        
        // Build final message ...
        $message = '['.self::$process.':'.$level.'] '.$message;
        
        // ... and give it to defined facilities
        foreach(self::$facilities as $facility) {
            // Filter based on process if facility requires it
            if(array_key_exists('process', $facility)) {
                $accepted = array_filter(array_map('trim', preg_split('`[\s,|]`', $facility['process'])));
                if(!in_array('*', $accepted) && !in_array(self::$process, $accepted))
                    continue;
            }
            
            // Filter based on level if facility requires it
            if(array_key_exists('level', $facility)) {
                $max = self::$levels[$facility['level']];
                if(self::$levels[$level] > $max) continue;
            }
            
            // Forward to facility related method with config
            $method = get_called_class().'::'.$facility['method'];
            call_user_func($method, $facility, $level, $message);
        }
    }
    
    /**
     * Log message to error_log (stderr)
     * 
     * @param string $message
     */
    private static function logErrorLog($facility, $level, $message) {
        error_log($message);
    }
    
    /**
     * Log message to file
     * 
     * @param string $message
     */
    private static function logFile($facility, $level, $message) {
        // Build file path
        $file = $facility['path'];
        $ext = '';
        if(preg_match('`^(.*/)?([^/]+)\.([a-z0-9]+)$`i', $file, $m)) {
            $file = $m[1].$m[2];
            $ext = $m[3];
        }else if(substr($file, -1) == '/') {
            $file .= 'filesender';
            $ext = 'log';
        }
        
        // Extend name if process separated files enabled
        if(array_key_exists('separate_processes', $facility)) $file .= '_'.self::$process;
        
        // Extend name if rotation is enabled
        if(array_key_exists('rotate', $facility)) switch($facility['rotate']) {
            case 'hourly' :  $file .= '_'.date('Y-m-d').'_'.date('H').'h'; break;
            case 'daily' :   $file .= '_'.date('Y-m-d'); break;
            case 'weekly' :  $file .= '_'.date('Y').'_week_'.date('W'); break;
            case 'monthly' : $file .= '_'.date('Y-m'); break;
            case 'yearly' :  $file .= '_'.date('Y'); break;
        }
        
        // Add file extension
        if($ext) $file .= '.'.$ext;
        
        // Write to file, log with PHP internal logger if any problem to avoid loops
        if($fh = fopen($file, 'a')) {
            if(flock($fh, LOCK_EX)) { // Try to lock for writing
                fwrite($fh, '['.date('Y-m-d H:i:s').'] '.trim($message)."\n");
                
                flock($fh, LOCK_UN); // Unlock file
            } else {
                self::logErrorLog(null, 'error', '[Filesender logging error] Could not aquire lock on '.$file);
            }
            
            fclose($fh);
        }else{
            self::logErrorLog(null, 'error', '[Filesender logging error] Could not log to '.$file);
            self::logErrorLog(null, 'error', $message);
        }
    }
    
    /**
     * Log message to syslog
     * 
     * @param string $message
     */
    private static function logSyslog($facility, $level, $message) {
        $priorities = array(LOG_ERR, LOG_WARNING, LOG_INFO, LOG_DEBUG);
        syslog($priorities[self::$levels[$level]], $message);
    }
    
    /**
     * Log message to callback
     * 
     * @param string $message
     */
    private static function logCallable($facility, $level, $message) {
        $facility['callback'](self::$process, $level, $message);
    }
    
    
    /**
     * Log an activity message on datebase
     * 
     * @param string $logEvent
     * @param object $target
     * @param object $author
     */
    public static function logActivity($logEvent, $target, $author = null){
        AuditLog::create($logEvent, $target, $author);
        StatLog::create($logEvent, $target);
        
        self::info('Event#'.$logEvent.' on '.(string)$target.($author ? ' by '.(string)$author : ''));
    }
}
