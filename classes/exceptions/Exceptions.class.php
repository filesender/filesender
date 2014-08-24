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
                    Logger::error(
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
     * Public exception details
     */
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
    public function __construct($msg_code, $internal_details, $public_details = null) {
        $this->uid = uniqid();
        
        $this->details = $public_details;
        
        if(!$internal_details) $internal_details = array();
        if(!is_array($internal_details)) $internal_details = array($internal_details);
        
        if($public_details) {
            if(!is_array($public_details)) $public_details = array($public_details);
            $internal_details = array_merge($public_details, $internal_details);
        }
        
        $log = array(
            'exception' => $msg_code,
            'trace' => explode("\n", $this->getTraceAsString()),
            'details' => array(),
        );
        
        foreach ($internal_details as $detail) {
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
    
    /**
     * Info getter
     * 
     * @return mixed the exception info
     */
    public function getDetails() {
        return $this->details;
    }
}
