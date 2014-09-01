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
 * REST server
 */
class RestServer {
    /**
     * Process the request
     * 
     * @throws lots of various exceptions
     */
    public static function process() {
        try {
            @session_start();
            
            // Get request data
            
            $path = array();
            if(array_key_exists('PATH_INFO', $_SERVER)) $path = array_filter(explode('/', $_SERVER['PATH_INFO']));
            
            $method = null;
            foreach(array('X_HTTP_METHOD_OVERRIDE', 'REQUEST_METHOD') as $k) {
                if(!array_key_exists($k, $_SERVER)) continue;
                $method = strtolower($_SERVER[$k]);
            }
            
            RestException::setContext('method', $method);
            if(!in_array($method, array('get', 'post', 'put', 'delete'))) throw new RestException('rest_method_not_allowed', 405);
            
            $endpoint = array_shift($path);
            if(!$endpoint) throw new RestException('rest_endpoint_missing', 400);
            RestException::setContext('endpoint', $endpoint);
            
            $request = new RestRequest();
            
            $input = AuthRemoteRequest::body(); // Because php://input can only be read once for PUT requests we rely on a central getter in AuthRemoteRequest
            
            $type = array_key_exists('CONTENT_TYPE', $_SERVER) ? $_SERVER['CONTENT_TYPE'] : null;
            if(!$type && array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) $type = $_SERVER['HTTP_CONTENT_TYPE'];
            
            $type_parts = array_map('trim', explode(';', $type));
            $type = array_shift($type_parts);
            $request->properties['type'] = $type;
            
            $type_properties = array();
            foreach($type_parts as $part) {
                $part = array_map('trim', explode('=', $part));
                if(count($part) == 2) $request->properties[$part[0]] = $part[1];
            }
            
            // Parse body
            switch($type) {
                case 'text/plain' :
                    $request->rawinput = trim($input);
                    break;
                
                case 'application/octet-stream' :
                    $request->rawinput = $input;
                    break;
                
                case 'application/x-www-form-urlencoded' :
                    $data = array();
                    parse_str($input, $data);
                    $request->input = (object)$data;
                    break;
                
                case 'application/json' :
                default :
                    $request->input = json_decode(trim($input));
            }
            
            //Check authentication
            Auth::isAuthenticated();
            if(Auth::isRemoteApplication()) {
                $application = AuthRemoteRequest::application();
                if(array_key_exists($endpoint, $application['acl'])) {
                    $acl = $application['acl'][$endpoint];
                }else if(array_key_exists('*', $application['acl'])) {
                    $acl = $application['acl']['*'];
                }else throw new RestException('rest_access_forbidden', 403);
                
                if(is_array($acl)) {
                    $macl = false;
                    if(array_key_exists($method, $acl)) {
                        $macl = $acl[$method];
                    }else if(array_key_exists('*', $acl)) {
                        $macl = $acl['*'];
                    }
                    
                    if(!$macl) throw new RestException('rest_method_not_allowed', 403);
                }else if(!$acl) throw new RestException('rest_access_forbidden', 403);
            }
            
            // JSONP specifics
            if(array_key_exists('callback', $_GET) && ($method != 'get')) throw new RestException('rest_jsonp_get_only', 405);
            
            // Get response filters
            foreach($_GET as $k => $v) switch($k) {
                case 'count' :
                case 'startIndex' :
                    if(preg_match('`^[0-9]+$`', $v)) $request->$k = (int)$v;
                    break;
                
                case 'format' :
                    break;
                
                case 'filterOp' :
                    if(is_array($v)) {
                        $request->filterOp = new stdClass();
                        foreach(array('equals', 'startWith', 'contains', 'present') as $k)
                            if(array_key_exists($k, $v)) $request->filterOp->$k = $v[$k];
                    }
                    break;
                
                case 'sortOrder' :
                    if(in_array($v, array('ascending', 'descending'))) $request->sortOrder = $v;
                    break;
                
                case 'updatedSince' :
                    // updatedSince takes ISO date, relative N days|weeks|months|years format and epoch timestamp (UTC)
                    $updatedSince = null;
                    if(preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(Z|[+-][0-9]{2}:[0-9]{2})$`', $v)) {
                        // ISO date
                        $localetz = new DateTimeZone(Config::get('Default_TimeZone'));
                        $offset = $localetz->getOffset(new DateTime($v));
                        $updatedSince = strtotime($v) + $offset;
                    }else if(preg_match('`^([0-9]+)\s*(hour|day|week|month|year)s?$`', $v, $m)) {
                        // Relative N day|days|week|weeks|month|months|year|years format
                        $updatedSince = strtotime('-'.$m[1].' '.$m[2]);
                    }else if(preg_match('`^[0-9]+$`', $v)) $updatedSince = (int)$v; // Epoch timestamp
                    
                    if(!$updatedSince || !is_numeric($updatedSince))
                        throw new RestException('rest_updatedsince_bad_format', 400, array('since' => $updatedSince));
                    
                    $request->updatedSince = $updatedSince;
                    break;
            }
            
            // Forward to handler
            $class = 'RestEndpoint'.ucfirst($endpoint);
            if(!file_exists(FILESENDER_BASE.'/classes/rest/endpoints/'.$class.'.class.php')) // Avoids CoreFileNotFoundException from Autoloader
                throw new RestException('endpoint_not_implemented', 501);
                
            $handler = new $class($request);
            if(!method_exists($handler, $method)) throw new RestException('method_not_implemented', 501);
            
            $data = call_user_func_array(array($handler, $method), $path);
            
            // Output data
            if(array_key_exists('callback', $_GET)) {
                header('Content-Type: text/javascript');
                echo $_GET['callback'].'('.json_encode($data).');';
                exit;
            }
            
            header('Content-Type: application/json');
            if(($method == 'post') && $data) {
                RestUtilities::sendResponseCode(201);
                if(substr($data['path'], 0, 1) != '/') $data['path'] = '/'.$data['path'];
                header('Location: '.Config::get('site_url').'rest.php'.$data['path']);
                $data = $data['data'];
            }
            echo json_encode($data);
            
        } catch(Exception $e) { // Return exceptions as HTTP errors
            $code = $e->getCode();
            if($code < 400 || $code >= 600) $code = 500;
            RestUtilities::sendResponseCode($code);
            header('Content-Type: application/json');
            echo json_encode(array(
                'message' => $e->getMessage(),
                'uid' => method_exists($e, 'getUid') ? $e->getUid() : null,
                'details' => method_exists($e, 'getDetails') ? $e->getDetails() : null
            ));
        }
    }
}
