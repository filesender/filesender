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


////////////////////////////////////////////
// Relevant exceptions declared here:
// Some can also be turned into singleton classes
////////////////////////////////////////////    

/**
 * Logging exception
 */
class LoggingException extends Exception {
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
    public function __construct($msg_code, $log = null) {
        $this->uid = uniqid();
        
        if(!$log) $log = $msg_code;
        if ($log && (!is_array($log) || !preg_match('`[a-z]`', key($log))))
            $log = array('exception' => $log);
        
        if ($log) 
            foreach ($log as $category => $lines) {
                if (!is_array($lines)) 
                    $lines = array($lines);
                
                foreach ($lines as $line) 
                    Logger::log(
                        '['.get_class($this).' '.
                        $category.' uid:'.$this->uid.'] '.$line
                    ); //insert get_class($this) before $category (concatenate them)
            }
        
        parent::__construct($msg_code);
    }
    
    /**
     * Uid getter
     * 
     * @return string the exception uid
     */
    public function getUid() {
        return $this->uid;
    }
}

/**
 * Detailed exception
 */
class DetailedException extends LoggingException {
    /**
     * Constructor
     * 
     * Logs all info to server log
     * 
     * @param string $msg_code message code to be used to present error
     * @param mixed $details details to log
     */
    public function __construct($msg_code /*, details*/) {
        $this->uid = uniqid();
        
        $log = array(
            'exception' => $msg_code,
            'trace' => explode("\n", $this->getTraceAsString()),
            'details' => array(),
        );
        
        $details = func_get_args();
        array_shift($details); // shift msg_code
        
        foreach ($details as $detail) {
            if (is_scalar($detail)) {
                $log['details'][] = $detail;
            } else {
                foreach (explode("\n", print_r($detail, true)) as $line) {
                    $log['details'][] = $line;
                }
            }
        }
        parent::__construct($msg_code, $log);
    }
}


/** 
 *  Default exception class for authentication-related exceptions. Outputs info to log.
 *
 *  @param string $lMsg: message to write to log.
 *  @param bool $error:  Type of log entry. E_ERROR if true, E_NOTICE if false.
 */
class AuthException extends DetailedException
{
    public function __construct($lMsg, $error)
    {
        if ($error) {
            logEntry($lMsg, "E_ERROR");
        } else {
            logEntry($lMsg, "E_NOTICE");
        }
    }
}


/**
 *  Thrown when the method requires a user to be authenticated and he isn't.
 *  -- or when user authentication attributes were invalid / not found.
 *  @params $log = parent::$lMsg, $error = parent::$error
 *  @param string $uMsg: message to user.
 */
class UserAuthException extends AuthException
{
    public function __construct($uMsg, $log, $error)
    {
        parent::__construct($log, $error);
        //TO_USER($uMsg);
    }
}


/**
 * Bad size format exception - used in Utilities class
 */
class BadSizeFormatException extends DetailedException
{
    /**
     * Constructor
     * 
     * @param string $size the raw, badly formated size
     */
    public function __construct($size)
    {
        parent::__construct(
            'bad_size_format', // Message to give to the user
            'size : '.$size // Details to log
        );
    }
}
