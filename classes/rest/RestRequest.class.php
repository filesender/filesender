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
 * REST request
 */
class RestRequest {
    /**
     * Properties of the request (content type, charset ...)
     */
    public $properties = array();
    
    /**
     * Request body
     */
    private $input = null;
    
    /**
     * Output properties the client asked for
     */
    public $count = null;
    public $startIndex = null;
    public $format = null;
    public $filterOp = null;
    public $sortOrder = null;
    public $updatedSince = null;
    
    /**
     * Getter
     */
    public function __get($key) {
        if($key == 'input') {
            return $this->input;
        }else throw new PropertyAccessException($this, $key);
    }
    
    /**
     * Setter
     */
    public function __set($key, $value) {
        if($key == 'input') {
            $this->input = new RestInput($value);
        }else if($key == 'rawinput') {
            $this->input = $value;
        }else throw new PropertyAccessException($this, $key);
    }
}

/**
 * Request body converter
 */
class RestInput {
    /**
     * Body data holder
     */
    private $data = array();
    
    /**
     * Fill from data
     */
    public function __construct($data) {
        if(!is_array($data)) $data = (array)$data;
        
        $this->data = $data;
    }
    
    /**
     * Getter
     */
    public function __get($key) {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }
}
