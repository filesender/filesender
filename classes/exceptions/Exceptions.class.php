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
 * Logging exception
 */
class LoggingException extends Exception
{
    /**
     * Holds exception unique id
     */
    private $uid = null;
    
    /**
     * Constructor
     *
     * Logs all info to server log
     *
     * @param string $msg_code message code to be used to present error
     * @param mixed $log lines to log by categories
     */
    public function __construct($msg_code, $log = null)
    {
        if (!$this->uid) {
            $this->uid = uniqid();
        }
        
        // normalize arguments
        if (!$log) {
            $log = $msg_code;
        }
        if ($log && (!is_array($log) || !preg_match('`[a-z]`', key($log)))) {
            $log = array('exception' => $log);
        }
        
        // Log info
        if ($log) {
            foreach ($log as $category => $lines) {
                if (!is_array($lines)) {
                    $lines = array($lines);
                }
                
                foreach ($lines as $line) {
                    $this->log($category, $line);
                }
            }
        }
        
        parent::__construct($msg_code);
    }

    /**
     * Log an exception line with the detail message groupmsg.
     * You can use groupmsg to cluster data from a single array into
     * a block in the log so folks know where the dump starts and ends.
     *
     * @param string $groupmsg something about the line
     * @param string $line     the line to log
     */
    public function log($groupmsg, $line)
    {
        Logger::error(
            '['.get_class($this).' '.
            $groupmsg.' uid:'.$this->getUid().'] '.$line
        );
    }

    /**
     * Log the whole array as a series of lines with log($groupmsg,$line)
     */
    public function logArray($groupmsg, $arr)
    {
        foreach ($arr as $line) {
            LoggingException::log($groupmsg, $line);
        }
    }
    
    /**
     * Uid getter
     *
     * @return string the exception uid
     */
    public function getUid()
    {
        if (!$this->uid) {
            $this->uid = uniqid();
        }
        return $this->uid;
    }
}

/**
 * Detailed exception
 */
class DetailedException extends LoggingException
{
    /**
     * Public exception details
     */
    private $uid = null;
    private $details = null;
    
    /**
     * Constructor
     *
     * Logs all info to server log
     *
     * @param string $msg_code message code to be used to present error
     * @param mixed $internal_details details to log
     * @param mixed $public_details details to give to the user (logged as well)
     */
    public function __construct($msg_code, $internal_details = null, $public_details = null)
    {
        $this->uid = uniqid();
        
        // Build data
        $this->details = $public_details;
        
        if (!$internal_details) {
            $internal_details = array();
        }
        if (!is_array($internal_details)) {
            $internal_details = array($internal_details);
        }
        
        if ($public_details) {
            if (!is_array($public_details)) {
                $public_details = array($public_details);
            }
            $internal_details = array_merge($public_details, $internal_details);
        }
        
        $log = array(
            'exception' => $msg_code,
            'trace' => explode("\n", $this->getTraceAsString()),
            'details' => array(),
        );

        $log['details'] = $this->convertArrayToLogArray($internal_details);
        parent::__construct($msg_code, $log);
    }

    /**
     * Convert the array $arr to a logable array which is suitable
     * to be passed to LoggingException::__construct()
     *
     * @param array arr the array to convert to something that can be logged
     * @param string prefix optional prefix for each key so you can indent in the log
     *
     * @return array the same data as $arr but in a log friendly format
     */
    public static function convertArrayToLogArray($arr, $prefix='')
    {
        $ret = array();
        foreach ($arr as $key => $detail) {
            $key = is_int($key) ? '' : $key.' = ';
            
            if (is_scalar($detail)) {
                if (is_bool($detail)) {
                    $detail = $detail ? 'true' : 'false';
                } elseif (!is_int($detail) && !is_float($detail)) {
                    $detail = '"'.$detail.'"';
                }
                
                $ret[] = $prefix.$key.$detail;
            } else {
                foreach (explode("\n", print_r($detail, true)) as $line) {
                    $ret[] = $prefix.$key.$line;
                }
            }
        }
        return $ret;
    }
    
    /**
     * Info getter
     *
     * @return mixed the exception info
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * return true if the needle (exception name) matches the config
     * for additional verbose logging
     */
    public static function additionalLoggingDesired($needle)
    {
        if (Utilities::configMatch(
            'exception_additional_logging_regex',
                                    $needle
        )) {
            return true;
        }
        return false;
    }

    /**
     */
    public function additionalLogFile($msg, $file)
    {
        if ($file) {
            $this->log($msg, 'file size:' . $file->size);
            $this->log($msg, 'file name:' . $file->name);
            $this->log($msg, 'file uid:'  . $file->uid);
        }
    }
}

/**
 * Storable exception
 */
class StorableException
{
    /**
     * Holds exception unique id
     */
    private $uid = null;
    
    /**
     * Public exception details
     */
    private $details = null;
    
    /**
     * Message
     */
    private $message = null;
    
    /**
     * Constructor
     *
     * @param Exception $exception
     */
    public function __construct($exception)
    {
        if (is_array($exception)) {
            // Got array, extract data from specific keys
            foreach (array('message', 'uid', 'details') as $p) {
                if (array_key_exists($p, $exception)) {
                    $this->$p = $exception[$p];
                }
            }
            
            return;
        }
        
        // Got Exception (child), get data from it
        
        $this->message = $exception->getMessage();
        
        if ($exception instanceof LoggingException) {
            $this->uid = $exception->getUid();
        }
        
        if ($exception instanceof DetailedException) {
            $this->details = $exception->getDetails();
        }
    }
    
    /**
     * Message getter
     *
     * @return string the exception message
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Uid getter
     *
     * @return string the exception uid
     */
    public function getUid()
    {
        return $this->uid;
    }
    
    /**
     * Info getter
     *
     * @return mixed the exception info
     */
    public function getDetails()
    {
        return $this->details;
    }
    
    /**
     * Serialize exception for path transmission
     *
     * @return string
     */
    public function serialize()
    {
        return base64_encode(json_encode(array(
            'message' => $this->message,
            'uid' => $this->uid,
            'details' => $this->details,
        )));
    }
    
    /**
     * Unerialize exception from path transmission
     *
     * @param string $serialized
     *
     * @return StorableException
     *
     * @throws DetailedException
     */
    public static function unserialize($serialized)
    {
        $exception = (array)json_decode(base64_decode($serialized));
        
        array_walk_recursive($exception, function(&$value) {
            $value = preg_replace('`\{(tr:)*(cfg|conf|config):([^}]+)\}`', '', $value);
        });

        if (!array_key_exists('message', $exception)) {
            throw new DetailedException('not_an_exception', $exception);
        }
        
        return new self($exception);
    }
}
