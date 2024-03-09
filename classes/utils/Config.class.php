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

if (!defined('FILESENDER_BASE')) {        // Require environment (fatal)
    die('Missing environment');
}

/**
 * Configuration class
 *
 * Provides configuration access with virtualhost support, default values,
 * special loaders and on the fly call of lambda function defined parameters.
 */
class Config
{
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
     * Merge down
     *
     * @param array $target
     * @param array $set
     */
    private static function merge(&$target, $set)
    {
        foreach ($set as $k => $v) {
            if (is_array($v) && array_filter(array_keys($v), function ($sk) {
                return !is_int($sk);
            }) && array_key_exists($k, $target)) {
                // Sub keys, merge
                self::merge($target[$k], $v);
            } else {
                $target[$k] = $v;
            }
        }
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
        if (!is_null(self::$parameters) && !$virtualhost) {
            return;
        } // Do not load twice, except if switching virtualhost
        
        // Load default configuration
        self::$parameters = array();
        
        // Load defaults if needed
        if (is_null(self::$defaults)) {
            self::$defaults = array();
            $defaults_file = FILESENDER_BASE.'/includes/ConfigDefaults.php';
            $default = array();

            if (file_exists($defaults_file)) {
                include_once($defaults_file);
            } //include: if file doesn't exists, execution will not stop, only gives a warning. if() not needed
            self::$defaults = $default;
        }
        
        // Check if main config exists
        $main_config_file = FILESENDER_BASE.'/config/config.php';
        if (!file_exists($main_config_file)) {
            throw new ConfigFileMissingException($main_config_file);
        }
        
        // Default config
        self::$parameters = self::$defaults;
        
        // Load base config
        $config = array();
        include_once($main_config_file);
        if ($virtualhost != null) {
            $config['virtualhost'] = $virtualhost;
        }


        self::merge(self::$parameters, $config);


        // load password file if it is there
        $pass_config_file = FILESENDER_BASE.'/config/config-passwords.php';
        if (file_exists($pass_config_file)) {
            $config = array();
            include_once($pass_config_file);
            self::merge(self::$parameters, $config);
        }

        // Load virtualhost config if used
        if ($virtualhost === null) {
            $virtualhost = self::get('virtualhost');
        }
        
        if ($virtualhost) {
            if (!is_string($virtualhost)) {
                throw new ConfigBadParameterException('virtualhost');
            }
            
            $config_file = FILESENDER_BASE.'/config/'.$virtualhost.'/config.php';
            if (!file_exists($config_file)) {
                throw new ConfigFileMissingException($config_file);
            } // Should exist even if empty
            
            $config = array();
            include_once $config_file;
            
            self::merge(self::$parameters, $config);
        }

        // Load config regex overrides if used and present
        $auth_config_regex_files = self::get('auth_config_regex_files');
        if( !empty($auth_config_regex_files) && is_array($auth_config_regex_files) && Auth::isAuthenticated()) {
                $auth_attrs = Auth::attributes();
                foreach ($auth_config_regex_files as $attr=>$regex_and_configs) {
                        if (!is_array($regex_and_configs)) {
                                continue;
                        }
                        foreach ($regex_and_configs as $regex => $extra_config_name) {
                                if (preg_match('`'.$regex.'`', $auth_attrs[$attr])) {
                                        $extra_config_file = FILESENDER_BASE.'/config/config-' . $extra_config_name . '.php';
                                        if (file_exists($extra_config_file)) {
                                                $config = array();
                                                include_once($extra_config_file);
                                                self::merge(self::$parameters, $config);
                                        }
                                }
                        }
                }
        }

        // ensure mandatory config settings file exists
        $mandatory_config_file = FILESENDER_BASE.'/includes/ConfigMandatorySettings.php';
        if (!file_exists($mandatory_config_file)) {
            throw new ConfigBadParameterException('Mandatory config settings file is missing. '
                                                + 'Please recheck your filesender php code is all installed. '
                                                + 'Looking for ' . $mandatory_config_file);
        }
        
        // load mandatory config settings
        $config = array();
        include_once($mandatory_config_file);
        self::merge(self::$parameters, $config);
        


        // Load config overrides if any
        $overrides_cfg = self::get('config_overrides');
        if ($overrides_cfg) {
            $overrides_file = FILESENDER_BASE.'/config/'.($virtualhost ? $virtualhost.'/' : '').'config_overrides.json';
            
            $overrides = file_exists($overrides_file) ? json_decode(trim(file_get_contents($overrides_file))) : new StdClass();
            
            self::$override = array('file' => $overrides_file, 'parameters' => array());
            foreach ($overrides_cfg as $key => $dfn) {
                // Casting
                if (is_string($dfn)) {
                    $dfn = array('type' => $dfn);
                } elseif (is_array($dfn) && !array_key_exists('type', $dfn)) {
                    $dfn = array('type' => 'enum', 'values' => $dfn);
                } elseif (!is_array($dfn)) {
                    throw new ConfigBadParameterException('config_overrides');
                }
                
                $dfn['value'] = property_exists($overrides, $key) ? $overrides->$key : null;
                
                self::$override['parameters'][$key] = $dfn;
            }
        }

        // shorter access again for checks
        $config = self::$parameters;
        
        // Special parameter checks and sets
        if (self::get('encryption_enabled')) {
            if (self::get('upload_chunk_size') < 262144) {
                throw new ConfigBadParameterException('upload_chunk_size must be >= 262144 if encryption is available');
            }
        }

        // If we are to store files in chunks then we need to make sure that
        // the size of uploaded chunks is the same as the download chunk size
        if (Config::get('storage_type') == 'filesystemChunked') {
            if (self::get('upload_chunk_size') != self::get('download_chunk_size')) {
                throw new ConfigBadParameterException('When storing files as chunks then upload_chunk_size must be the same as download_chunk_size');
            }
        }

        // see the error message for info
        if (Utilities::startsWith(strtolower(Config::get('storage_type')), 'cloud')) {
            if (self::get('upload_chunk_size') != self::get('download_chunk_size')) {
                throw new ConfigBadParameterException('When storing files using the Cloud storage the upload_chunk_size must be the same as download_chunk_size');
            }
        }

        // update max_system_upload_size if php.ini post_max_size and upload_max_filesize is set lower
        $max_system_upload_size = min(
            Utilities::sizeToBytes(ini_get('post_max_size')) - 2048,
            Utilities::sizeToBytes(ini_get('upload_max_filesize'))
        );
        
        // Some defaults for undefined values
        
        self::$parameters['max_legacy_file_size'] = min(
            self::$parameters['max_legacy_file_size'],
            $max_system_upload_size
        );
        
        if (self::$override && array_key_exists('max_legacy_file_size', self::$override['parameters'])) {
            self::$override['parameters']['max_legacy_file_size']['value'] = min(
                self::$override['parameters']['max_legacy_file_size']['value'],
                $max_system_upload_size
            );
        }
        
        if (!self::get('default_transfer_days_valid')) {
            self::$parameters['default_transfer_days_valid'] = self::get('max_transfer_days_valid');
        }
        
        if (!self::get('max_guest_days_valid')) {
            self::$parameters['max_guest_days_valid'] = self::get('max_transfer_days_valid');
        }
        if (!self::get('min_guest_days_valid') || self::get('min_guest_days_valid') < 0) {
            self::$parameters['min_guest_days_valid'] = 1;
        }
        
        if (!self::get('default_guest_days_valid')) {
            self::$parameters['default_guest_days_valid'] = self::get('max_guest_days_valid');
        }

        // verify user settings
        if (array_key_exists("log_facilities", $config)
            && !Utilities::is_array_of_array($config["log_facilities"])) {
            throw new ConfigMissingParameterException(
                'log_facilities[]',
            'Maybe you have set $config["log_facilities"] = array("type" => "file",...) instead of $config["log_facilities"] = array(array("type" => "file",...))'
            );
        }

        if (!self::get('encryption_generated_password_length')) {
            self::$parameters['encryption_generated_password_length'] = self::get('encryption_min_password_length');
        }
        if (self::get('encryption_min_password_length') > self::get('encryption_generated_password_length')) {
            throw new ConfigBadParameterException('Generated password length must be equal or greater than encryption_min_password_length');
        }
        if (self::get('encryption_password_text_only_min_password_length') > 0
            && self::get('encryption_min_password_length') >= self::get('encryption_password_text_only_min_password_length') ) {
            throw new ConfigBadParameterException('The encryption_password_text_only_min_password_length setting must be greater than encryption_min_password_length');
        }

        // If the admin has very small chunks then they will have a smaller max file size.
        self::$parameters['crypto_gcm_max_file_size'] = 4294967296 * self::$parameters['upload_chunk_size'];
        self::$parameters['crypto_gcm_max_chunk_size']  = 4294967295 * 16;
        self::$parameters['crypto_gcm_max_chunk_count'] = 4294967295;
        

        $relay_to = Config::get('relay_unknown_feedbacks');
        switch($relay_to) {
            case 'support':
                $support = Config::get('support_email');
                if(!strlen($support)) {
                    throw new ConfigBadParameterException('Setting relay_unknown_feedbacks to support means that support_email MUST also be set');
                }
                break;
        }

        $crypt_padding_size = self::get('upload_crypted_chunk_padding_size');
        if ((self::get('upload_chunk_size')+$crypt_padding_size) != self::get('upload_crypted_chunk_size')) {
            throw new ConfigBadParameterException('You must set upload_crypted_chunk_size to upload_chunk_size + '.$crypt_padding_size.'.');
        }

        $themeName = self::get('theme');
        if( strlen($themeName) && preg_match('@[./]@',$themeName)) {
            throw new ConfigBadParameterException('the theme config can not contain / or . characters');
        }

        if( self::get('terasender_enabled')) {
            if( self::get('terasender_worker_count') < 1 || self::get('terasender_worker_count') > 30 ) {
                throw new ConfigBadParameterException('terasender_worker_count must be between 1 and 30 inclusive.');
            }
        }

        if( self::get('crypto_pbkdf2_expected_secure_to_year')) {
            $y = self::get('crypto_pbkdf2_expected_secure_to_year');
            if( $y < 2021 ) {
                throw new ConfigBadParameterException('crypto_pbkdf2_expected_secure_to_year must be above 2021.');
            }
            $iterations = Crypto::getPBKDF2IterationCountForYear($y);
            self::$parameters['encryption_password_hash_iterations_new_files'] = $iterations;
        }

        //
        // you can not autogenerate the secret and expect the user to accept aup
        // at the same time.
        if( self::get('api_secret_aup_enabled')) {
            self::$parameters['auth_remote_user_autogenerate_secret'] = false;
        }

        $v = self::get('upload_page_password_can_not_be_part_of_message_handling');
        if( !$v || ($v != '' && $v != 'none' && $v != 'warning' && $v != 'error' )) {
            self::$parameters['upload_page_password_can_not_be_part_of_message_handling'] = 'warning';
        }

        // force to bool
        $v = Utilities::isTrue(self::get('data_protection_user_frequent_email_address_disabled'));
        self::$parameters['data_protection_user_frequent_email_address_disabled'] = $v;

        self::forceLoadedToBool('guest_support_enabled');

        $kv = self::get('encryption_key_version_new_files');
        // these are crypto_app.js / crypto_key_version_constants
        //   v2019_gcm_importKey_deriveKey: 3, // AES-GCM otherwise same as v2018_importKey_deriveKey
        //   v2019_gcm_digest_importKey:    2, // AES-GCM otherwise same as v2017_digest_importKey
        //   v2018_importKey_deriveKey:     1, // AES-CBC
        //   v2017_digest_importKey:        0  // AES-CBC
        if( $kv == 2 || $kv == 3 ) {
            self::$parameters['crypto_crypt_name'] = "AES-GCM";
        } else {
            self::$parameters['crypto_crypt_name'] = "AES-CBC";
        }

        self::$parameters['download_verification_code_valid_duration_minutes'] = floor(self::$parameters['download_verification_code_valid_duration'] / 60);

        $k = 'storage_filesystem_per_day_min_days_to_clean_empty_directories';
        if( -1 == self::$parameters[$k] ) {
            self::$parameters[$k] = self::$parameters['max_transfer_days_valid'];
            $kmax = 'storage_filesystem_per_day_max_days_to_clean_empty_directories';
            if( self::$parameters[$kmax] < self::$parameters[$k] ) {
                self::$parameters[$kmax] = self::$parameters[$k] + 30;
            }
        }

        if( Config::get("storage_filesystem_per_day_max_days_to_clean_empty_directories") < Config::get("storage_filesystem_per_day_min_days_to_clean_empty_directories")) {
            throw new ConfigBadParameterException("storage_filesystem_per_day_max_days_to_clean_empty_directories must be larger than storage_filesystem_per_day_min_days_to_clean_empty_directories");
        }
        
        // verify classes are happy
        Guest::validateConfig();
        ClientLog::validateConfig();
    }

   /**
    * This makes sure the value for $k is a boolean using isTrue() to coerce.
    *
    * @return the updated value for Config::get($k) which is also assigned internally.
    */
    private static function forceLoadedToBool($k)
    {
        $v = Config::get($k);
        $v = Utilities::isTrue($v);
        self::$parameters[$k] = $v;
        return $v;
    }
    
    public static function performLongerValidation()
    {
        Config::get('db_database');

        echo "performing longer configuration validation...\n";
        Logger::info("You have executed checkconfig.php to perform longer validation...");
        
        $allowSlowerTests = true;
        Guest::validateConfig($allowSlowerTests);
        ClientLog::validateConfig($allowSlowerTests);
        
        echo "longer configuration validation has passed\n";
    }
            
    /**
     * Get virtualhosts list
     *
     * @return array virtualhosts names
     */
    public static function getVirtualhosts()
    {
        $virtualhosts = array();
        foreach (scandir(FILESENDER_BASE.'/config') as $item) {
            if (!preg_match('`^(.+)\.conf\.php$`', $item, $match)) {
                continue;
            }
            $virtualhosts[] = $match[1];
        }
        return $virtualhosts;
    }
    
    /**
     * Run code for each virtualhost (allow usage of Config class in sub code)
     *
     * @param callable $callback code called for each virtualhost
     */
    public static function callUponVirtualhosts($callback)
    {
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
    private static function evalParameter($key, $value, $args)
    {
        if (is_callable($value) && !is_string($value)) {
            // Takes care of lambda functions and array($obj, 'method') callables
            $value = call_user_func_array($value, $args);
        } elseif (is_string($value)) {
            if (preg_match('`^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*::[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$`', $value, $m)) { // Is it an allowed method name ?
                if (is_callable($value)) {
                    $value = call_user_func_array($value, $args);
                } // Does it exists ?
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
    public static function get()
    {
        // Load config if not already done
        self::load();
        
        $args = func_get_args();
        $key = array_shift($args);
        
        // Do we require a family ?
        if (substr($key, -1) == '*') {
            $search = substr($key, 0, -1);
            $set = array();
            array_unshift($args, null); // Prepare place for key for sub-calls
            foreach (array_keys(self::$parameters) as $key) {
                if (substr($key, 0, strlen($search)) == $search) {
                    $args[0] = $key;
                    $set[substr($key, strlen($search))] = call_user_func_array(static::class.'::get', $args);
                }
            }
            return $set;
        }
        
        // Undef returns null
        if (!array_key_exists($key, self::$parameters)) {
            return null;
        }
        
        $value = self::$parameters[$key];
        
        // If final value already cached return it
        if (in_array($key, self::$cached_parameters)) {
            return $value;
        }
        
        // Evaluate otherwise
        $value = self::evalParameter($key, $value, $args);
        
        // Managing '/' on site_url
        if ($key == 'site_url') {
            if (substr($value, -1) != '/') {
                $value .= '/';
            }
        }

        // Apply override if any
        if (
            is_array(self::$override) &&
            array_key_exists($key, self::$override['parameters']) &&
            !is_null(self::$override['parameters'][$key]['value'])
        ) {
            self::$override['parameters'][$key]['base'] = $value;
            $value = self::$override['parameters'][$key]['value'];
        }
        
        // Cache and return
        self::$parameters[$key] = $value;
        self::$cached_parameters[] = $key;
        
        return $value;
    }

    public static function getArray($key)
    {
        return Utilities::ensureArray(Config::get($key));
    }
    
    /**
     * Get default value (without override)
     *
     * @param string $key parameter name
     * @param mixed $* arguments to forward to callable if defined
     *
     * @return mixed the parameter value or null if parameter is unknown
     */
    public static function getBaseValue($key)
    {
        $value = call_user_func_array(static::class.'::get', func_get_args());
        
        if (
            is_array(self::$override) &&
            array_key_exists($key, self::$override['parameters']) &&
            array_key_exists('base', self::$override['parameters'][$key])
        ) {
            return self::$override['parameters'][$key]['base'];
        }
        
        return $value;
    }
    
    /**
     * Check if parameter exists
     *
     * @param string $key parameter name
     *
     * @return bool
     */
    public static function exists($key)
    {
        self::load();
        return array_key_exists($key, self::$parameters);
    }
    
    /**
     * Get overrides data
     *
     * @return array
     */
    public static function overrides()
    {
        self::load();
        if (!self::$override) {
            throw new ConfigOverrideDisabledException();
        }
        
        return self::$override['parameters'];
    }


    /**
     * Force set a key-value that is for this session only
     *
     * @param k key to set
     * @param v value to set
     */
    public static function localOverride($k, $v)
    {
        self::$parameters[$k] = $v;
        self::$cached_parameters[] = $k;
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
    public static function override(/* $set [, $value] [, $save = true] */)
    {
        // Load if not already done
        self::load();

        // If override allowed ?
        if (!self::$override) {
            throw new ConfigOverrideDisabledException();
        }

        $args = func_get_args();
        $set = array_shift($args);
        
        // Apply any changes
        if ($set) {
            if (!is_array($set)) {
                $set = array($set => array_shift($args));
            }
            
            foreach ($set as $k => $v) {
                // Is override of this parameter allowed ?
                if (!array_key_exists($k, self::$override['parameters'])) {
                    throw new ConfigOverrideNotAllowedException($k);
                }
                
                // Apply any defined validators, throw if failure
                if (array_key_exists('validator', self::$override['parameters'][$k])) {
                    $validators = self::$override['parameters'][$k]['validator'];
                    if (!is_array($validators)) {
                        $validators = array($validators);
                    }
                    
                    if (!is_null($v)) {
                        foreach ($validators as $n => $validator) {
                            if (is_callable($validator) && !$validator($v)) {
                                throw new ConfigOverrideValidationFailedException($k, is_string($validator) ? $validator : 'custom:'.$n);
                            }
                        }
                    }
                }
                
                // Cache new value
                self::$override['parameters'][$k]['value'] = $v;
            }
        }
        
        // Do we need to save something ?
        $save = count($args) ? array_shift($args) : true;
        if ($save) {
            // Gather values without ones that got back to default
            $overrides = array();
            foreach (self::$override['parameters'] as $k => $dfn) {
                if (!is_null($dfn['value'])) {
                    $overrides[$k] = $dfn['value'];
                }
            }
            
            $file = self::$override['file'];
            
            // Save if any overrides, remove overrides file otherwise
            if (count($overrides)) {
                if ($fh = fopen($file, 'w')) {
                    fwrite($fh, json_encode($overrides));
                    fclose($fh);
                } else {
                    throw new CoreCannotWriteFileException($file);
                }
            } else {
                if (file_exists($file) && !unlink($file)) {
                    throw new CoreCannotDeleteFileException($file);
                }
            }
        }
    }

}

/**
 * Config validator helper
 *
 * Every error leads to php error logging as Logger may not have what it needs to work already
 */
class ConfigValidator
{
    /**
     * Checks
     */
    private static $checks = array();
    
    /**
     * Add new check(s)
     *
     * @param string $parameter_name
     * @param mixed * test(s)
     */
    public static function addCheck($parameter_name)
    {
        if (!$parameter_name) {
            error_log('ConfigValidator check needs parameter_name');
        }
        
        if (!array_key_exists($parameter_name, self::$checks)) {
            self::$checks[$parameter_name] = array();
        }
        
        foreach (array_slice(func_get_args(), 1) as $arg) {
            if (is_string($arg)) {
                $ored = array();
                foreach (array_map('trim', explode('|', $arg)) as $p) {
                    if (preg_match('`^(is_)?(set|not_empty|null|bool|string|int|float|array|callable)$`', $p)) {
                        $ored[] = $p;
                    } else {
                        error_log('ConfigValidator bad check for "'.$parameter_name.'" : '.print_r($p, true));
                    }
                }
                
                self::$checks[$parameter_name][] = $ored;
            } elseif (is_callable($arg)) {
                self::$checks[$parameter_name][] = $arg;
            }
        }
    }
    
    /**
     * Run checks
     */
    public static function run()
    {
        $all_pass = true;
        
        foreach (self::$checks as $parameter => $checks) {
            $value = Config::get($parameter);
            
            foreach ($checks as $ored) {
                $pass = false;
                $error = '';
                if (is_array($ored)) {
                    $error = '"'.implode('|', $ored).'"';
                    
                    foreach ($ored as $check) {
                        if (substr($check, 0, 3) == 'is_') {
                            $check = substr($check, 3);
                        }
                        
                        switch ($check) {
                            case 'set':         $pass |= Config::exists($parameter);    break;
                            case 'not_empty':   $pass |= !empty($value);                break;
                            case 'null':        $pass |= ($value === null);             break;
                            case 'bool':        $pass |= is_bool($value);               break;
                            case 'string':      $pass |= is_string($value);             break;
                            case 'int':         $pass |= is_int($value);                break;
                            case 'float':       $pass |= is_float($value);              break;
                            case 'array':       $pass |= is_array($value);              break;
                            case 'callable':    $pass |= is_callable($value);           break;
                        }
                        
                        if ($pass) {
                            break;
                        } // Stop on first true or component
                    }
                } elseif (is_callable($ored)) {
                    $pass = $ored($value, $error);
                }
                
                if (!$pass) {
                    error_log('ConfigValidator "'.$parameter.'" does not validate check : '.$error);
                    $all_pass = false;
                    break;
                }
            }
        }
        
        if (!$all_pass) {
            die('Configuration error(s), please check the server logs or ask an admin to do so.');
        }
    }
}
