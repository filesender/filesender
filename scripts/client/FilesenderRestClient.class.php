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
     * Authentication mode
     */
    private $mode = null;
    
    /**
     * Application name or user uid
     */
    private $application_or_uid = null;
    
    /**
     * Signing secret
     */
    private $secret = null;
    
    /**
     * Upload chunk size
     */
    public $chunk_size = null;
    
    /**
     * Response headers holder
     */
    private static $headers = array();
    
    /**
     * Constructor
     * 
     * @param string $base_url base url to Filesender's rest service
     * @param string $mode authentication mode, "application" or "user"
     * @param string $application_or_uid the application name or user uid
     * @param string $secret signing secret
     */
    public function __construct($base_url, $mode, $application_or_uid, $secret) {
        if(!$base_url || !$mode || !in_array($mode, array('application', 'user')) || !$application_or_uid) throw new Exception('Missing application id');
        
        $this->base_url = $base_url;
        $this->mode = $mode;
        $this->application_or_uid = $application_or_uid;
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
    private function call($method, $path, $args = array(), $content = null, $options = array())
    {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if( is_null($args)) {
            $args = array();
        }
    
        if(!in_array($method, array('get', 'post', 'put', 'delete'))) throw new Exception('Method is not allowed', 405);
        
        if(substr($path, 0, 1) != '/') $path = '/'.$path;
        if($path == '/') throw new Exception('Endpoint is missing', 400);
        
        if($this->mode == 'application') {
            $args['remote_application'] = $this->application_or_uid;
        } else if($this->mode == 'user') {
            $args['remote_user'] = $this->application_or_uid;
        }
        
        $args['timestamp'] = time();
        ksort($args);
        
        $signed = $method.'&'.preg_replace('`https?://`', '', $this->base_url).$path;
        
        $signed .= '?'.implode('&', $this->flatten($args));
        
        $content_type = 'application/json';
        if(array_key_exists('Content-Type', $options))
            $content_type = $options['Content-Type'];
        
        if($content) {
            $input = ($content_type == 'application/json') ? json_encode($content) : $content;
            $signed .= '&'.$input;
        }
        
        $args['signature'] = hash_hmac('sha1', $signed, $this->secret);
        
        $url = $this->base_url.$path.'?'.implode('&', $this->flatten($args));
        
        $h = curl_init();
        curl_setopt($h, CURLOPT_URL, $url);
        if($content) curl_setopt($h, CURLOPT_POSTFIELDS, $input);
        curl_setopt($h, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: '.$content_type
        ));
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($h, CURLOPT_SSL_VERIFYPEER, true);
        
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
    public function get($path, $args = array(), $options = array()) {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if( is_null($args)) {
            $args = array();
        }
        return $this->call('get', $path, $args, $options);
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
    public function post($path, $args = array(), $content = null, $options = array()) {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if( is_null($args)) {
            $args = array();
        }
        return $this->call('post', $path, $args, $content, $options);
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
    public function put($path, $args = array(), $content = null, $options = array()) {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if( is_null($args)) {
            $args = array();
        }
        return $this->call('put', $path, $args, $content, $options);
    }
    
    /**
     * Make a DELETE request
     * 
     * @param string $path path to make the request to (under the rest service)
     * @param array $args GET arguments
     * 
     * @return mixed the response
     */
    public function delete($path, $args = array(), $options = array()) {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if( is_null($args)) {
            $args = array();
        }
        return $this->call('delete', $path, $args, $options);
    }
    
    /**
     * Get info about the Filesender instance
     */
    public function getInfo() {
        return $this->get('/info');
    }
    
    /**
     * Start a transfer
     * 
     * @param string $user_id (will be ignored if remote user authentication in use)
     * @param string $from sender email
     * @param array $files array of file arrays with name and size entries
     * @param array $recipients array of recipients addresses
     * @param string $subject optionnal subject
     * @param string $message optionnal message
     * @param string $expires expiry date (yyyy-mm-dd or unix timestamp)
     * @param array $options array of selected option identifiers
     */
    public function postTransfer($user_id, $from, $files, $recipients, $subject = null, $message = null, $expires = null, $options = array())
    {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        if(!is_array($recipients)) $recipients = array($recipients);
        
    
        if(!$expires) {
            $info = $this->getInfo();
            if(!property_exists($info, 'default_transfer_days_valid'))
                throw new Exception('Expires missing and not default value in info to build it from');
            $expires = time() + (int)$info->default_transfer_days_valid * 24*3600;
        }
        
        return $this->post('/transfer', array('remote_user' => $user_id), array(
            'from' => $from,
            'files' => $files,
            'recipients' => $recipients,
            'subject' => $subject,
            'message' => $message,
            'expires' => $expires,
            'options' => $options
        ));
    }
    
    /**
     * Post a file chunk
     * 
     * @param object file
     * @param string binary chunk
     */
    public function postChunk($file, $chunk) {
        return $this->post(
            '/file/'.$file->id.'/chunk',
            array('key' => $file->uid),
            $chunk,
            array('Content-Type' => 'application/octet-stream')
        );
    }
    
    /**
     * Put a file chunk
     * 
     * @param object file
     * @param blob chunk
     * @param int offset
     */
    public function putChunk($file, $chunk, $offset) {
        return $this->put(
            '/file/'.$file->id.'/chunk/'.$offset,
            array('key' => $file->uid),
            $chunk,
            array('Content-Type' => 'application/octet-stream')
        );
    }
    
    /**
     * Signal file completion (along with checking data)
     * 
     * @param object file
     */
    public function fileComplete($file) {
        return $this->put(
            '/file/'.$file->id,
            array('key' => $file->uid),
            array('complete' => true)
        );
    }
    
    /**
     * Signal transfer completion (along with checking data)
     * 
     * @param object transfer
     */
    public function transferComplete($transfer) {
        return $this->put(
            '/transfer/'.$transfer->id,
            array('key' => $transfer->files[0]->uid),
            array('complete' => true)
        );
    }
    
    /**
     * Delete a transfer
     * 
     * @param mixed transfer object or transfer id
     */
    public function deleteTransfer($transfer) {
        $id = is_object($transfer) ? $transfer->id : (int)$transfer;
        
        $args = array();
        if(is_object($transfer))
            $args['key'] = $transfer->files[0]->uid;
        
        return $this->delete('/transfer/'.$id, $args);
    }
    
    /**
     * Upload files to recipients
     * 
     * @param string $user_id (will be ignored if remote user authentication in use)
     * @param string $from sender email
     * @param mixed $files file path or array of files path
     * @param array $recipients array of recipients addresses
     * @param string $subject optional subject
     * @param string $message optional message
     * @param string $expires expiry date (yyyy-mm-dd or unix timestamp)
     * @param array $options array of selected option identifiers
     *
     * recipients and options are converted to an array for you if you pass a single entry
     * or null respectively.
     */
    public function sendFiles($user_id, $from, $filespath, $recipients, $subject = null, $message = null, $expires = null, $options = array())
    {
        // be a little flexible with null options,
        // convert them to empty array for user
        if( is_null($options)) {
            $options = array();
        }
        
        $info = $this->getInfo();
        if(!$this->chunk_size || !$expires) {
            
            if(!$expires) {
                if(!property_exists($info, 'default_transfer_days_valid'))
                    throw new Exception('Expires missing and not default value in info to build it from');
                $expires = time() + (int)$info->default_transfer_days_valid * 24*3600;
            }
            
            if(!$this->chunk_size) {
                if(!property_exists($info, 'upload_chunk_size'))
                    throw new Exception('Chunk size missing and not value in info to build it from');
                $this->chunk_size = (int)$info->upload_chunk_size;
            }
        }
            
        if(property_exists($info, 'upload_chunk_size'))
            $this->chunk_size = (int)$info->upload_chunk_size;
        
        $files = array();
        
        if(!is_array($filespath)) $filespath = array($filespath);
        foreach($filespath as $path) {
            if(!is_string($path)) throw new Exception('Not a file path : '.print_r($path, true));
            
            if(!file_exists($path)) throw new Exception('File not found : '.$path);
            
            $name = basename($path);
            $size = filesize($path);
            $files[$name.':'.$size] = array(
                'name' => $name,
                'size' => $size,
                'path' => $path
            );
        }
        
        if(!is_array($recipients)) $recipients = array($recipients);
        
        $transfer = $this->postTransfer($user_id, $from, array_values(array_map(function($file) {
            return array(
                'name' => $file['name'],
                'size' => $file['size']
            );
        }, $files)), $recipients, $subject, $message, $expires, $options)->created;
        
        try {
            foreach($transfer->files as $file) {
                $path = $files[$file->name.':'.$file->size]['path'];
                $size = $files[$file->name.':'.$file->size]['size'];
                
                $fh = fopen($path, 'rb');
                if(!$fh) throw new Exception('Cannot read file '.$path);
                
                for($offset=0; $offset<=$size; $offset+=$this->chunk_size) {
                    $data = fread($fh, $this->chunk_size);
                    
                    $this->putChunk($file, $data, $offset);
                }
                
                fclose($fh);
                
                $this->fileComplete($file);
            }
            
            $this->transferComplete($transfer);
        } catch(Exception $e) {
            $this->deleteTransfer($transfer);
            
            throw $e;
        }
    }
}
