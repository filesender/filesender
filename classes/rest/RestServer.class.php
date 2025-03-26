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
 * REST server
 */
class RestServer
{
    public static function sanitizeCallback($cb)
    {
        $callback = preg_replace('`[^a-z0-9_\.-]`i', '', $cb);
        return $callback;
    }

    public static function validateCallback_iframe_callback($cb)
    {
        $acceptable = array("legacyUploadResultHandler");
        if (in_array($cb, $acceptable)) {
            return $cb;
        }
        return '';
    }
    
    public static function validateCallback_callback($cb)
    {
        $acceptable = array("lang.setTranslations");
        if (in_array($cb, $acceptable)) {
            return $cb;
        }
        return '';
    }
    

    /**
     * Process the request
     *
     * @throws lots of various exceptions
     */
    public static function process()
    {
        try {
            
            // Get authentication state (fills auth data in relevant classes)
            // Will also start a session using the authentication handler.
            // This prevents problems caused by duplicate sessions.
            Auth::isAuthenticated();

            // If undergoing maintenance report it as an error
            if (Config::get('maintenance')) {
                throw new RestException('undergoing_maintenance', 503);
            }
            
            // Split request path to get tokens
            $path = array();
            if (array_key_exists('PATH_INFO', $_SERVER)) {
                $path = array_filter(explode('/', $_SERVER['PATH_INFO']));
            }

            // Get method from possible headers
            $method = Utilities::getHTTPMethod();

            // Record called method (for log), fail if unknown
            RestException::setContext('method', $method);
            if (!in_array($method, array('get', 'post', 'put', 'delete'))) {
                throw new RestException('rest_method_not_allowed', 405);
            }
            
            // Get endpoint (first token), fail if none
            $endpoint = array_shift($path);
            if (!$endpoint) {
                throw new RestException('rest_endpoint_missing', 400);
            }
            RestException::setContext('endpoint', $endpoint);
            
            // Request data accessor
            $request = new RestRequest();
            
            // Because php://input can only be read once for PUT requests we rely on a shared getter
            $input = Request::body();
            
            // Get request content type from possible headers
            $type = array_key_exists('CONTENT_TYPE', $_SERVER) ? $_SERVER['CONTENT_TYPE'] : null;
            if (!$type && array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $type = $_SERVER['HTTP_CONTENT_TYPE'];
            }
            if(!$type) {
                $type = '';
            }
            
            // Parse content type
            $type_parts = array_map('trim', explode(';', $type));
            $type = array_shift($type_parts);
            $request->properties['type'] = $type;
            
            $type_properties = array();
            foreach ($type_parts as $part) {
                $part = array_map('trim', explode('=', $part));
                if (count($part) == 2) {
                    $request->properties[$part[0]] = $part[1];
                }
            }
            
            Logger::debug('Got "'.$method.'" request for endpoint "'.$endpoint.'/'.implode('/', $path).'" with '.strlen($input).' bytes payload');
            
            // Parse body
            switch ($type) {
                case 'text/plain':
                    $request->rawinput = trim(Utilities::sanitizeInput($input));
                    break;
                
                case 'application/octet-stream':
                    // Don't sanitize binary input !
                    $request->rawinput = $input;
                    break;
                
                case 'application/x-www-form-urlencoded':
                    $data = array();
                    parse_str($input, $data);
                    $request->input = (object)Utilities::sanitizeInput($data);
                    break;
                
                case 'application/json':
                default:
                    $request->input = json_decode(trim(Utilities::sanitizeInput($input)));
            }
            
            $security_token = null;
            $security_token_matches = false;
            
            if (Auth::isRemoteApplication()) {
                // Remote applications must honor ACLs
                $application = AuthRemote::application();
                
                if (!$application->allowedTo($method, $endpoint)) {
                    throw new RestException('rest_access_forbidden', 403);
                }
            } elseif (Auth::isRemoteUser()) {
                // Nothing peculiar to do
            } elseif (in_array($method, array('post', 'put', 'delete'))) {
                // SP or Guest, lets do XSRF check
                $token_name = 'HTTP_X_FILESENDER_SECURITY_TOKEN';
                $security_token = array_key_exists($token_name, $_SERVER) ? $_SERVER[$token_name] : '';
                
                if ($method == 'post' && array_key_exists('security-token', $_POST)) {
                    $security_token = $_POST['security-token'];
                }
                
                // Do not fail now since some endpoints may not require it
                $security_token_matches = $security_token && Utilities::checkSecurityToken($security_token);
            }

            // if configured, ensure no nasty CSRF is going on
            Security::validateAgainstCSRF( true );
            
            // JSONP specifics
            if (array_key_exists('callback', $_GET)) {
                if ($method != 'get') {
                    throw new RestException('rest_jsonp_get_only', 405);
                }
            }
                
            if (array_key_exists('callback', $_GET) || array_key_exists('iframe_callback', $_REQUEST)) {
                $allowed_by_default = array('/info', '/lang', '/file/[0-9]+/whole');
                
                if (Config::get('auth_remote_user_enabled')) {
                    $allowed_by_default[] = '/user/@me/remote_auth_config';
                }

                $allow_jsonp = array_filter(array_merge((array)Config::get('rest_allow_jsonp'), $allowed_by_default));
                
                $res = '/'.$endpoint.'/'.implode('/', $path);
                
                $allowed = false;
                foreach ($allow_jsonp as $a) {
                    $a = str_replace('`', '\\`', $a);
                    if (!preg_match('`'.$a.'`', $res)) {
                        continue;
                    }
                    $allowed = true;
                    break;
                }
                
                if (!$allowed) {
                    throw new RestException('rest_jsonp_not_allowed', 405);
                }
            }
            
            // Get response filters
            foreach ($_GET as $k => $v) {
                switch ($k) {
                case 'count':
                case 'startIndex':
                    if (preg_match('`^[0-9]+$`', $v)) {
                        $request->$k = (int)$v;
                    }
                    break;
                
                case 'format':
                    break;
                
                case 'filterOp':
                    if (is_array($v)) {
                        foreach ($v as $p => $f) {
                            $request->filterOp[$p] = array();
                            foreach (array('equals', 'startWith', 'contains', 'present') as $k) {
                                if (is_array($f)) {
                                    if (array_key_exists($k, $f)) {
                                        $request->filterOp[$p][$k] = $f[$k];
                                    }
                                }
                            }
                        }
                    }
                    break;
                
                case 'sortOrder':
                    if (in_array($v, array('ascending', 'descending'))) {
                        $request->sortOrder = $v;
                    }
                    break;
                
                case 'updatedSince':
                    // updatedSince takes ISO date, relative N days|weeks|months|years format and epoch timestamp (UTC)
                    $updatedSince = null;
                    if (preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(Z|[+-][0-9]{2}:[0-9]{2})$`', $v)) {
                        // ISO date
                        $localetz = new DateTimeZone(Config::get('default_timezone'));
                        $offset = $localetz->getOffset(new DateTime($v));
                        $updatedSince = strtotime($v) + $offset;
                    } elseif (preg_match('`^([0-9]+)\s*(hour|day|week|month|year)s?$`', $v, $m)) {
                        // Relative N day|days|week|weeks|month|months|year|years format
                        $updatedSince = strtotime('-'.$m[1].' '.$m[2]);
                    } elseif (preg_match('`^[0-9]+$`', $v)) {
                        $updatedSince = (int)$v;
                    } // Epoch timestamp
                    
                    if (!$updatedSince || !is_numeric($updatedSince)) {
                        throw new RestException('rest_updatedsince_bad_format', 400, array('since' => $updatedSince));
                    }
                    
                    $request->updatedSince = $updatedSince;
                    break;
            }
            }
            
            // Forward to handler, fail if unknown or method not implemented
            $class = 'RestEndpoint'.ucfirst($endpoint);
            if (!file_exists(FILESENDER_BASE.'/classes/rest/endpoints/'.$class.'.class.php')) { // Avoids CoreFileNotFoundException from Autoloader
                throw new RestException('rest_endpoint_not_implemented', 501);
            }
                
            $handler = new $class($request);
            if (!method_exists($handler, $method)) {
                throw new RestException('rest_method_not_implemented', 501);
            }
            
            if (
                !AuthRemote::isAuthenticated() &&
                in_array($method, array('post', 'put', 'delete')) &&
                $handler->requireSecurityTokenMatch($method, $path) &&
                !$security_token_matches
            ) {
                throw new RestInvalidSecurityTokenException('session token = '.Utilities::getSecurityToken().' and token = '.$security_token);
            }

            Logger::debug('Forwarding call to '.$class.'::'.$method.'() handler');
            
            $data = call_user_func_array(array($handler, $method), $path);
            
            Logger::debug('Got data to send back');

            // Security that applies to all page requests
            Security::addHTTPHeaders();

            //
            // Output data
            //
            if (array_key_exists('callback', $_GET)) {
                header('Content-Type: text/javascript');
                $callback = self::sanitizeCallback($_GET['callback']);
                $callback = self::validateCallback_callback($callback);
                
                if ($callback) {
                    echo $callback.'('.json_encode($data).');';
                }
                exit;
            }
            
            if (array_key_exists('iframe_callback', $_GET)) {
                header('Content-Type: text/html');
                $callback = self::sanitizeCallback($_GET['iframe_callback']);
                $callback = self::validateCallback_iframe_callback($callback);
                if ($callback) {
                    echo '<html><body><script type="text/javascript">window.parent.'.$callback.'('.json_encode($data).');</script></body></html>';
                }
                exit;
            }

            header('Content-Type: application/json');
            if (($method == 'post') && $data) {
                RestUtilities::sendResponseCode(201);
                if (substr($data['path'], 0, 1) != '/') {
                    $data['path'] = '/'.$data['path'];
                }
                header('Location: '.Config::get('site_url').'rest.php'.$data['path']);
                $data = $data['data'];
            }
            echo json_encode($data);
        } catch (Exception $e) { // Return exceptions as HTTP errors
            $code = $e->getCode();
            if ($code < 400 || $code >= 600) {
                $code = 500;
            }
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
