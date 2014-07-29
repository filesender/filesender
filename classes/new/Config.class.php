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

if (substr(dirname(__FILE__), -3) == "new") {
    // Use base exceptions
    require_once FILESENDER_BASE.'/classes/new/Exceptions.class.php';

    // Use utilities
    require_once FILESENDER_BASE.'/classes/new/Utilities.class.php';
} else {
    // Use base exceptions
    require_once FILESENDER_BASE.'/classes/Exceptions.class.php';

    // Use utilities
    require_once FILESENDER_BASE.'/classes/Utilities.class.php';
}

/**
 * Configuration class
 * 
 * Provides configuration access with virtualhost support, default values,
 * special loaders and on the fly call of lambda function defined parameters.
 * 
 * Implements arrayaccess to support old, array based, configuration access,
 * it should disapear in the long run
 */
class Config implements arrayaccess {
    /**
     * Holds instance of class for old singleton access, should disapear
     * 
     * @deprecated 2.0
     */
    private static $instance = null;
    
    /**
     * Default parameters values
     * 
     * Null if not already loaded, array otherwise
     */
    private static $defaults = null;
    
    /**
     * Special parameter processing map
     * 
     * Null if not already loaded, array otherwise
     */
    private static $processors = null;
    
    /**
     * Actual parameters' values, raw from config file or evaluated
     * 
     * Null if not already loaded, array otherwise
     */
    private static $parameters = null;
    
    /**
     * List of already evaluated parameters' keys (special loaders, lambda functions)
     */
    private static $cached_parameters = array();
    
    /**
     * Old singleton getter, should be replaced by Config::get calls for efficiency's sake
     * 
     * @deprecated 2.0
     * 
     * @return self single instance to use
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::load();
            self::$instance = new self();
        }
        self::logOldAccess();
        return self::$instance;
    }
    public function loadConfig()
    {
        return self::getInstance();
    }
    
    /**
     * Constructor for old access support
     */
    private function __construct() {}
    
    /**
     * Old config parameter existence checking support
     * 
     * @deprecated 2.0
     * 
     * @param mixed $key id of config parameter to check
     * 
     * @return bool whether the parameter exists
     */
    public function offsetExists($key)
    {
        self::logOldAccess();
        return array_key_exists($key, self::$parameters);
    }
    
    /**
     * Old config parameter getter
     * 
     * @deprecated 2.0
     * 
     * @param mixed $key id of config parameter to get
     * 
     * @return mixed the parameter value or null if parameter is unknown
     */
    public function offsetGet($key)
    {
        self::logOldAccess();
        return self::get($key);
    }
    
    /**
     * Old config parameter setter (should not be called)
     * 
     * @deprecated 2.0
     * 
     * @param mixed $key id of config parameter to set
     * @param mixed $value value to set
     * 
     * @throws ConfigCannotSetException
     */
    public function offsetSet($key, $value)
    {
        self::logOldAccess();
        throw new ConfigCannotSetException();
    }
    
    /**
     * Old config parameter remover (should not be called)
     * 
     * @deprecated 2.0
     * 
     * @param mixed $key id of config parameter to delete
     * 
     * @throws ConfigCannotSetException
     */
    public function offsetUnset($key)
    {
        self::logOldAccess();
        throw new ConfigCannotSetException();
    }
    

    /**
     * Instance based getter
     * 
     * @deprecated 2.0
     * 
     * @return mixed the parameter value or null if parameter is unknown
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    


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
        
        // Load processors map if needed
        if (is_null(self::$processors)) {
            self::$processors = array();
            $processors_file = FILESENDER_BASE.'/includes/ConfigProcessors.php';
            $processor = array();

            if (file_exists($processors_file))
                include_once($processors_file);     
            self::$processors = $processor;
        }
        
        // Check if main config exists
        $main_config_file = FILESENDER_BASE.'/config/config.array.php';
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
            // TODO add exceptions
            if (!is_string($virtualhost))
                throw new ConfigBadParameterException('virtualhost');
            
            $config_file = FILESENDER_BASE.'/config/'.$virtualhost.'.conf.php';
            if (!file_exists($config_file))
                throw new ConfigFileMissingException($config_file); // Should exist even if empty
            
            $config = array();
            include_once $config_file;
            foreach ($config as $key => $value)
                self::$parameters[$key] = $value; // Merge
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
     * 
     * Apply any matching processors
     * 
     * @throws ConfigUnknownProcessorException
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
        
        if (array_key_exists($key, self::$processors)) {
            $processors = is_array(self::$processors[$key]) ? self::$processors[$key] : array(self::$processors[$key]);
            array_unshift($args, $value);
            
            foreach ($processors as $processor) {
                $method = 'proccess'.ucfirst($processor);
                if (!method_exists(get_class(), $method))
                    throw new ConfigUnknownProcessorException($processor);
                $value = call_user_func_array(get_class().'::'.$method, $args);
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
    public function get() {
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
        
        self::$parameters[$key] = $value;
        self::$cached_parameters[] = $key;
        
        return $value;
    }
    
    /**
     * Log old access style for removal
     */
    private static function logOldAccess() {
        if (!self::get('debug'))
            return; // Log only in debug mode
        
        $file = FILESENDER_BASE.'/log/old_config_access.log';
        if ($f = fopen($file, 'a')) {
            $trace = debug_backtrace();
            $trace = $trace[2];
            
            fwrite($f, 'Old config access in '.$trace['file'].' at line '.$trace['line']."\n");
            //fwrite($f, debug_backtrace
            fclose($f);
        }
    }
}

/**
 * Missing file exception
 */
class ConfigFileMissingException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $file path of the required file
     */
    public function __construct($file) {
        parent::__construct(
            'config_file_missing', // Message to give to the user
            'File '.$file.' not found' // Details to log
        );
    }
}

/**
 * Bad parameter exception
 */
class ConfigBadParameterException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $key name of the bad parameter
     */
    public function __construct($key) {
        parent::__construct(
            'config_bad_parameter', // Message to give to the user
            'parameter : '.$key // Details to log
        );
    }
}

/**
 * Cannot set exception
 */
class ConfigCannotSetException extends DetailedException {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'config_cannot_set' // Message to give to the user
        );
    }
}


/**
 * Cannot set exception
 */
class ConfigUnknownProcessorException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $processor name of the required processor which is missing
     */
    public function __construct($processor) {
        parent::__construct(
            'config_unknown_processor', // Message to give to the user
            'processor : '.$processor // Details to log
        );
    }
}
