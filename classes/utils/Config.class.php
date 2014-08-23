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

if (!defined('FILESENDER_BASE'))        // Require environment (fatal)
    die('Missing environment');

/**
 * Configuration class
 * 
 * Provides configuration access with virtualhost support, default values,
 * special loaders and on the fly call of lambda function defined parameters.
 */
class Config {
    /**
     * Default parameters values
     * 
     * Null if not already loaded, array otherwise
     */
    private static $defaults = null;
    
    /**
     * Actual parameters' values, raw from config file or evaluated
     * 
     * Null if not already loaded, array otherwise
     */
    private static $parameters = null;
    
    /**
     * Parameters override stack
     */
    private static $override = null;
    
    /**
     * List of already evaluated parameters' keys (special loaders, lambda functions)
     */
    private static $cached_parameters = array();
    
    /**
     * Main loader, loads defaults, main config and virtualhost config if it exists
     * 
     * @param string $virtualhost the name of a particular virtualhost to load
     * 
     * @throws ConfigFileMissingException
     * @throws ConfigBadParameterException
     */
    private static function load($virtualhost = null)
    {
        if (!is_null(self::$parameters) && !$virtualhost) 
            return; // Do not load twice, except if switching virtualhost
        self::$parameters = array();
        
        // Load defaults if needed
        if (is_null(self::$defaults)) {
            self::$defaults = array();
            $defaults_file = FILESENDER_BASE.'/includes/ConfigDefaults.php';
            $default = array();

            if (file_exists($defaults_file))
                include_once($defaults_file); //include: if file doesn't exists, execution will not stop, only gives a warning. if() not needed
            self::$defaults = $default;
        }
        
        // Check if main config exists
        $main_config_file = FILESENDER_BASE.'/config/config.php';
        if (!file_exists($main_config_file))
            throw new ConfigFileMissingException($main_config_file);
        
        // Default config
        $config = self::$defaults;
        
        // Load base config
        include_once($main_config_file);
        if ($virtualhost != null)
            $config['virtualhost'] = $virtualhost;
        foreach ($config as $key => $value)
            self::$parameters[$key] = $value; // Merge
        
        // Load virtualhost config if used
        if ($virtualhost === null)
            $virtualhost = self::get('virtualhost');
            
        if ($virtualhost) {
            if (!is_string($virtualhost))
                throw new ConfigBadParameterException('virtualhost');
            
            $config_file = FILESENDER_BASE.'/config/'.$virtualhost.'/config.php';
            if (!file_exists($config_file))
                throw new ConfigFileMissingException($config_file); // Should exist even if empty
            
            $config = array();
            include_once $config_file;
            foreach ($config as $key => $value)
                self::$parameters[$key] = $value; // Merge
        }
        
        $overrides_cfg = self::get('config_overrides');
        if ($overrides_cfg) {
            $overrides_file = FILESENDER_BASE.'/config/'.($virtualhost ? $virtualhost.'/' : '').'config_overrides.json';
            
            $overrides = file_exists($overrides_file) ? json_decode(trim(file_get_contents($overrides_file))) : new StdClass();
            
            self::$override = array('file' => $overrides_file, 'parameters' => array());
            foreach($overrides_cfg as $key => $dfn) {
                // Casting
                if(is_string($dfn)) {
                    $dfn = array('type' => $dfn);
                } else if(is_array($dfn) && !array_key_exists('type', $dfn)) {
                    $dfn = array('type' => 'enum', 'values' => $dfn);
                } else if(!is_array($dfn))
                    throw new ConfigBadParameterException('config_overrides');
                
                $dfn['value'] = property_exists($overrides, $key) ? $overrides->$key : null;
                
                self::$override['parameters'][$key] = $dfn;
            }
        }
    }
    
    /**
     * Get virtualhosts list
     * 
     * @return array virtualhosts names
     */
    public static function getVirtualhosts() {
        $virtualhosts = array();
        foreach (scandir(FILESENDER_BASE.'/config') as $item) {
            if (!preg_match('`^(.+)\.conf\.php$`', $item, $match)) 
                continue;
            $virtualhosts[] = $match[1];
        }
        return $virtualhosts;
    }
    
    /**
     * Run code for each virtualhost (allow usage of Config class in sub code)
     * 
     * @param callable $callback code called for each virtualhost
     */
    public static function callUponVirtualhosts($callback) {
        $virtualhosts = self::getVirtualhosts();
        
        if (count($virtualhosts)) { // Using virtualhosts
            foreach ($virtualhosts as $name) {
                self::load($name);
                $callback();
            }
        } else { // Not using virtualhosts
            self::load();
            $callback();
        }
    }

    /**
     * Evaluate runtime configuration parameters (main scope function names are not proccessed)
     */
    private static function evalParameter($key, $value, $args) {
        if (is_callable($value) && !is_string($value)) {
            // Takes care of lambda functions and array($obj, 'method') callables
            $value = call_user_func_array($value, $args);
        } elseif (is_string($value)) {
            if (preg_match('`^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*::[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$`', $value, $m)) { // Is it an allowed method name ?
                if (is_callable($value)) $value = call_user_func_array($value, $args); // Does it exists ?
            }
        }
        
        return $value;
    }
    
    /**
     * Get value for a configuration parameter with callables evaluation
     * 
     * @param string $key parameter name
     * @param mixed $* arguments to forward to callable if defined
     * 
     * @return mixed the parameter value or null if parameter is unknown
     */
    public static function get() {
        self::load();
        $args = func_get_args();
        $key = array_shift($args);
        
        if (substr($key, -1) == '*') { // Do we require a family ?
            $search = substr($key, 0, -1);
            $set = array();
            array_unshift($args, null); // Prepare place for key for sub-calls
            foreach(array_keys(self::$parameters) as $key)
                if(substr($key, 0, strlen($search)) == $search) {
                    $args[0] = $key;
                    $set[substr($key, strlen($search))] = call_user_func_array(get_class().'::get', $args);
                }
            
            return $set;
        }
        
        if (!array_key_exists($key, self::$parameters))
            return null;
        
        $value = self::$parameters[$key];
        
        if (in_array($key, self::$cached_parameters))
            return $value;
        
        $value = self::evalParameter($key, $value, $args);
        
        if(
            is_array(self::$override) &&
            array_key_exists($key, self::$override['parameters']) &&
            !is_null(self::$override['parameters'][$key]['value'])
        ) {
            self::$override['parameters'][$key]['base'] = $value;
            $value = self::$override['parameters'][$key]['value'];
        }
        
        self::$parameters[$key] = $value;
        self::$cached_parameters[] = $key;
        
        return $value;
    }
    
    /**
     * Get default value (without override)
     * 
     * @param string $key parameter name
     * @param mixed $* arguments to forward to callable if defined
     * 
     * @return mixed the parameter value or null if parameter is unknown
     */
    public static function getBaseValue($key) {
        $value = call_user_func_array(get_class().'::get', func_get_args());
        
        if(
            is_array(self::$override) &&
            array_key_exists($key, self::$override['parameters']) &&
            array_key_exists('base', self::$override['parameters'][$key])
        ) return self::$override['parameters'][$key]['base'];
        
        return $value;
    }
    
    /**
     * Check if parameter exists
     * 
     * @param string $key parameter name
     * 
     * @return bool
     */
    public function exists($key) {
        self::load();
        return array_key_exists($key, self::$parameters);
    }
    
    /**
     * Get overrides data
     * 
     * @return array
     */
    public static function overrides() {
        self::load();
        if(!self::$override)
            throw new ConfigOverrideDisabledException();
        
        return self::$override['parameters'];
    }
    
    /**
     * Set override
     * 
     * Null values means go back to default value from config
     * 
     * @param mixed $set key or array of key-values
     * @param mixed $value (optionnal)
     * @param bool $save (optionnal)
     */
    public static function override(/* $set [, $value] [, $save = true] */) {
        self::load();
        if(!self::$override)
            throw new ConfigOverrideDisabledException();
        
        $args = func_get_args();
        
        $set = array_shift($args);
        if($set) {
            if(!is_array($set)) $set = array($set => array_shift($args));
            
            foreach($set as $k => $v) {
                if(!array_key_exists($k, self::$override['parameters']))
                    throw new ConfigOverrideNotAllowedException($k);
                
                if(array_key_exists('validator', self::$override['parameters'][$k])) {
                    $validators = self::$override['parameters'][$k]['validator'];
                    if(!is_array($validators)) $validators = array($validators);
                    
                    if(!is_null($v))
                        foreach($validators as $n => $validator)
                            if(is_callable($validator) && !$validator($v))
                                throw new ConfigOverrideValidationFailedException($k, is_string($validator) ? $validator : 'custom:'.$n);
                }
                
                self::$override['parameters'][$k]['value'] = $v;
            }
        }
        
        $save = count($args) ? array_shift($args) : true;
        if($save) {
            $overrides = array();
            
            foreach(self::$override['parameters'] as $k => $dfn) {
                if(!is_null($dfn['value']))
                    $overrides[$k] = $dfn['value'];
            }
            
            $file = self::$override['file'];
            
            if(count($overrides)) {
                if($fh = fopen($file, 'w')) {
                    fwrite($fh, json_encode($overrides));
                    fclose($fh);
                } else throw new ConfigOverrideCannotSaveException($file);
            } else {
                if(file_exists($file) && !unlink($file))
                    throw new ConfigOverrideCannotSaveException($file);
            }
        }
    }
}
