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

/**
 * REST client for Filesender
 */
class FilesenderRestClient {
    /**
     * Base url to Filesender's rest service
     */
    private $base_url = null;
    
    /**
     * Application name
     */
    private $application = null;
    
    /**
     * Signing secret
     */
    private $secret = null;
    
    /**
     * Response headers holder
     */
    private static $headers = array();
    
    /**
     * Constructor
     * 
     * @param string $base_url base url to Filesender's rest service
     * @param string $application the application name
     * @param string $secret signing secret
     */
    public function __construct($base_url, $application, $secret) {
        if(!$base_url || !$application) throw new Exception('Missing application id');
        
        $this->base_url = $base_url;
        $this->application = $application;
        $this->secret = $secret;
    }
    
    /**
     * Flatten arguments (recursive)
     * 
     * @param $a array multi-dimensionnal array
     * @param $p string parent key stack
     * 
     * @return array single dimension array
     */
    private function flatten($a, $p=null) {
        $o = array();
        ksort($a);
        foreach($a as $k => $v) {
            if(is_array($v)) {
                foreach($this->flatten($v, $p ? $p.'['.$k.']' : $k) as $s) $o[] = $s;
            }else $o[] = ($p ? $p.'['.$k.']' : $k).'='.$v;
        }
        return $o;
    }
    
    /**
     * Response header callback
     * 
     * @param mixed $o
     * @param string $h the header
     * 
     * @return string the length of the consumed header
     */
    public static function _responseHeader($o, $h) {
        $parts = array_map('trim', explode(':', $h, 2));
        $name = array_shift($parts);
        $value = array_shift($parts);
        if($name) self::$headers[$name] = $value;
        return strlen($h);
    }
    
    /**
     * Make a signed call to Filesender
     * 
     * @param string $method HTTP method to use
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * @param mixed $content request body
     * 
     * @return mixed the response
     * 
     * @throws Exception
     */
    private function call($method, $path, $args = array(), $content = null) {
        if(!in_array($method, array('get', 'post', 'put', 'delete'))) throw new Exception('Method is not allowed', 405);
        
        if(substr($path, 0, 1) != '/') $path = '/'.$path;
        if($path == '/') throw new Exception('Endpoint is missing', 400);
        
        $args['remote_application'] = $this->application;
        $args['timestamp'] = time();
        ksort($args);
        
        $signed = $method.'&'.preg_replace('`https?://`', '', $this->base_url).$path;
        
        $signed .= '?'.implode('&', $this->flatten($args));
        
        if($content) {
            $input = json_encode($content);
            $signed .= '&'.$input;
        }
        
        $args['signature'] = hash_hmac('sha1', $signed, $this->secret);
        
        $url = $this->base_url.$path.'?'.implode('&', $this->flatten($args));
        
        $h = curl_init();
        curl_setopt($h, CURLOPT_URL, $url);
        curl_setopt($h, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json'
        ));
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
        
        if($content) curl_setopt($h, CURLOPT_POSTFIELDS, $input);
        
        switch($method) {
            case 'get' : break;
            case 'post' :
                curl_setopt($h, CURLOPT_POST, true);
                break;
            
            case 'put' :
                curl_setopt($h, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            
            case 'delete':
                curl_setopt($h, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        curl_setopt($h, CURLOPT_HEADERFUNCTION, get_called_class().'::_responseHeader');
        
        $response = curl_exec($h);
        $error = curl_error($h);
        $code = (int)curl_getinfo($h, CURLINFO_HTTP_CODE);
        curl_close($h);
        
        if($error) throw new Exception('Client error : '.$error);
        
        if($code != 200) {
            if(($method != 'post') || ($code != 201)) {
                throw new Exception('Http error '.$code.($response ? ' : '.$response : ''));
            }
        }
        
        if(!$response) throw new Exception('Empty response');
        
        $response = json_decode($response);
        
        if($method != 'post') return $response;
        
        $r = new StdClass();
        $r->location = self::$headers['Location'];
        $r->created = $response;
        return $r;
    }
    
    /**
     * Validator for updatedSince parameter
     * 
     * @param mixed $value the raw parameter
     * 
     * @throws Exception
     */
    private function validateUpdatedSince($value) {
        if(
            !preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(Z|[+-][0-9]{2}:[0-9]{2})$`', $value) // ISO
            && !preg_match('`^([0-9]+)\s*(hour|day|week|month|year)s?$`', $value) // offset
            && !preg_match('`^[0-9]+$`', $value) // Epoch
        ) throw new Exception('Invalid updatedSince value');
    }
    
    /**
     * Make a GET request
     * 
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * 
     * @return mixed the response
     */
    public function get($path, $args = array()) {
        return $this->call('get', $path, $args);
    }
    
    /**
     * Make a POST request
     * 
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * @param mixed $content request body
     * 
     * @return mixed the response
     */
    public function post($path, $args = array(), $content = null) {
        return $this->call('post', $path, $args, $content);
    }
    
    /**
     * Make a PUT request
     * 
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * @param mixed $content request body
     * 
     * @return mixed the response
     */
    public function put($path, $args = array(), $content = null) {
        return $this->call('put', $path, $args, $content);
    }
    
    /**
     * Make a DELETE request
     * 
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * 
     * @return mixed the response
     */
    public function delete($path, $args = array()) {
        return $this->call('delete', $path, $args);
    }
    
    /**
     * Get info about the Filesender instance
     */
    public function getInfo() {
        return $this->get('/info');
    }
    
    /**
     * Get quota information
     */
    public function getQuota() {
        return $this->get('/quota');
    }
    
    /**
     * Get user quota information
     */
    public function userGetQuota($uid) {
        return $this->get('/quota', array('remote_user' => $uid));
    }
    
    public function userGetFrequentRecipients($uid, $filter = '') {
        $args = array('remote_user' => $uid);
        if($filter) $args['filterOp'] = array('contains' => $filter);
        return $this->get('/recipients', $args);
    }
    
    public function getTopUploaders($count = 10, $since = null) {
        $args = array('count' => $count);
        
        if($since) {
            $this->validateUpdatedSince($since);
            $args['updatedSince'] = $since;
        }
        
        return $this->get('/top/uploaders', $args);
    }
    
    public function getTopDownloads($count = 10, $since = null) {
        $args = array('count' => $count);
        
        if($since) {
            $this->validateUpdatedSince($since);
            $args['updatedSince'] = $since;
        }
        
        return $this->get('/top/downloads', $args);
    }
    
    public function userGetsCount($since = null) {
        $args = array();
        
        if($since) {
            $this->validateUpdatedSince($since);
            $args['updatedSince'] = $since;
        }
        
        return $this->get('/stats/users', $args);
    }
    
    public function getQuotaStats($since = null) {
        $args = array();
        
        if($since) {
            $this->validateUpdatedSince($since);
            $args['updatedSince'] = $since;
        }
        
        return $this->get('/stats/quota', $args);
    }
    
    public function getFilesStats($since = null) {
        $args = array();
        
        if($since) {
            $this->validateUpdatedSince($since);
            $args['updatedSince'] = $since;
        }
        
        return $this->get('/stats/files', $args);
    }
    
    public function userGetFiles($uid) {
        return $this->get('/files', array('remote_user' => $uid));
    }
    
    public function userGetReceivedFiles($uid) {
        return $this->get('/files/@recipient', array('remote_user' => $uid));
    }
    
    public function userGetFile($uid, $fileuid) {
        return $this->get('/files/'.$fileuid, array('remote_user' => $uid));
    }
    
    public function userDeleteFile($uid, $fileuid) {
        return $this->delete('/files/'.$fileuid, array('remote_user' => $uid));
    }
    
    public function userGetFileRecipients($uid, $fileuid) {
        return $this->get('/files/'.$fileuid.'/recipients', array('remote_user' => $uid));
    }
    
    public function userGetFileRecipient($uid, $fileuid, $vid) {
        return $this->get('/files/'.$fileuid.'/recipients/'.$vid, array('remote_user' => $uid));
    }
    
    public function userAddFileRecipient($uid, $fileuid, $email, $expirydate = null, $subject = null, $message = null) {
        $data = array('email' => $email);
        if($expirydate) $data['expirydate'] = $expirydate;
        if($subject) $data['subject'] = $subject;
        if($message) $data['message'] = $message;
        return $this->post('/files/'.$fileuid.'/recipients', array('remote_user' => $uid), $data);
    }
    
    public function userDeleteFileRecipient($uid, $fileuid, $vid) {
        return $this->delete('/files/'.$fileuid.'/recipients/'.$vid, array('remote_user' => $uid));
    }
    
    public function userGetVouchers($uid) {
        return $this->get('/vouchers', array('remote_user' => $uid));
    }
    
    public function userGetReceivedVouchers($uid) {
        return $this->get('/vouchers/@recipient', array('remote_user' => $uid));
    }
    
    public function userGetVoucher($uid, $vid) {
        return $this->get('/vouchers/'.$vid, array('remote_user' => $uid));
    }
    
    public function userDeleteVoucher($uid, $vid) {
        return $this->delete('/vouchers/'.$vid, array('remote_user' => $uid));
    }
    
    public function userAddVoucher($uid, $email, $expirydate = null, array $options = null) {
        $data = array('email' => $email);
        if($expirydate) $data['expirydate'] = $expirydate;
        if($options) $data['options'] = $options;
        return $this->post('/vouchers', array('remote_user' => $uid), $data);
    }
}
