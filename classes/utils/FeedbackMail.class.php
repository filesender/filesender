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
 * Mime feedback email parser
 */
class FeedbackMail
{
    private $headers = null;
    private $parts = array();
    
    /**
     * Constructor, private (see parse method)
     */
    private function __construct()
    {
    }
    
    /**
     * Parse email
     *
     * @param string $message
     *
     * @return self
     */
    public static function parse($message)
    {
        $o = new self();
        
        // Separate headers and body
        $message = preg_split('/\n\s*\n/', $message, 2);
        
        // Parse headers
        $o->headers = FeedbackMailHeaders::parse(array_shift($message));
        
        $message = array_shift($message);
        
        // Roughly parse body parts if any
        if ($o->headers->content_type_boundary) {
            $parts = explode('--'.$o->headers->content_type_boundary, $message);
            array_shift($parts);
            $parts = array_filter(array_map('trim', $parts));
            if (end($parts) == '--') {
                array_pop($parts);
            }
            
            foreach ($parts as $part) {
                $o->parts[] = FeedbackMailPart::parse($part);
            }
        } else {
            $o->parts[] = FeedbackMailPart::parse($message);
        }
        
        return $o;
    }
    
    /**
     * Getter
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return property_exists($this, $key) ? $this->$key : null;
    }
}

/**
 * Mime headers parser
 */
class FeedbackMailHeaders
{
    private $headers = array();
    
    /**
     * Constructor, private (see parse method)
     */
    private function __construct()
    {
    }
    
    /**
     * Parse email
     *
     * @param string $raw_headers
     *
     * @return self
     */
    public static function parse($raw_headers)
    {
        $o = new self();
        
        // Separate headers
        $entries = array();
        foreach (explode("\n", trim($raw_headers)) as $line) {
            if (preg_match('/^\s+/', $line)) {
                $entries[count($entries) - 1] .= ' '.trim($line);
            } else {
                $entries[] = trim($line);
            }
        }
        
        // Parse individual headers
        foreach ($entries as $entry) {
            // Skip if bad format
            if (preg_match('/^([^\s]+):(.+)$/', $entry, $m)) {
                $key = str_replace('-', '_', strtolower($m[1]));
                $value = trim(mb_decode_mimeheader($m[2]));
                
                // Handle Content-Type options
                if ($key == 'content_type') {
                    $sparts = array_map('trim', explode(';', $value));
                    $value = array_shift($sparts);
                    foreach ($sparts as $subtype) {
                        if (!strlen($subtype)) {
                            continue;
                        }
                        $subtypearray = array_map('trim', explode('=', $subtype, 2));
                        if( count($subtypearray) < 2 ) {
                            continue;
                        }
                        list($skey, $sval) = $subtypearray;
                        $skey = str_replace('-', '_', strtolower($skey));
                        if (preg_match('`^"(.+)"$`', $sval, $m)) {
                            $sval = $m[1];
                        }
                        $o->headers[$key.'_'.$skey] = $sval;
                    }
                }
                $o->headers[$key] = $value;
            }
        }
        
        return $o;
    }
    
    /**
     * Getter
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
    }
}

/**
 * Mime part parser
 */
class FeedbackMailPart
{
    private $headers = null;
    private $content = null;
    
    private static $parse_only = null;
    
    /**
     * Constructor, private (see parse method)
     */
    private function __construct()
    {
    }
    
    /**
     * Parse part
     *
     * @param string $part
     *
     * @return self
     */
    public static function parse($part)
    {
        $o = new self();
        
        // Separate part headers and body
        $part = preg_split('/\n\s*\n/', trim($part), 2);
        
        // Parse part headers
        $o->headers = FeedbackMailHeaders::parse(array_shift($part));
        
        // Do not parse body (less memory consuption) if not required part type
        if (self::$parse_only && $o->headers->content_type) {
            if (!preg_match('`^('.implode('|', array_map(function ($m) {
                return str_replace('*', '.*', $m);
            }, self::$parse_only)).')$`', $o->headers->content_type)) {
                return $o;
            }
        }
        
        // Fetch content
        $o->content = trim((string)array_shift($part));
        
        // Reverse transfer encoding
        switch ($o->headers->content_transfer_encoding) {
            case 'quoted-printable': $o->content = quoted_printable_decode($o->content); break;
            case 'base64': $o->content = base64_decode($o->content);
        }
        
        // Convert charset if different than utf8
        if ($o->headers->content_type_charset) {
            $o->content = iconv($o->headers->content_type_charset, 'UTF-8', $o->content);
        }
        
        return $o;
    }
    
    /**
     * Set parser allowed content types
     *
     * @param mixed $type string or array of strings
     */
    public static function parseOnly($type)
    {
        if (!is_array($type)) {
            $type = array($type);
        }
        
        foreach ($type as $rule) {
            self::$parse_only[] = $rule;
        }
    }
    
    /**
     * Getter
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return property_exists($this, $key) ? $this->$key : null;
    }
}
