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
 * Database access abstraction class
 *
 * Handles connexion setup, provides PDO instance methods shorthands and easing methods
 */
class DBI
{
    /**
     * Connexion data
     */
    private static $config = null;
    
    /**
     * Last request timestamp for ping
     */
    private static $last_request = null;
    
    /**
     * Connection instance (PDO)
     */
    private static $instance = null;
    
    /**
     * Connect to database
     *
     * @throws DBIConnexionMissingParameterException
     * @throws DBIConnexionException
     */
    private static function load()
    {
        // Get config, check mandatory parameters
        $config = Config::get('db_*');
        $config['dsn'] = Config::get('dsn');

        if(ConfigPrivate::havekey('db_username')) {
            $config['username'] = ConfigPrivate::get('db_username');
        }
        if(ConfigPrivate::havekey('db_password')) {
            $config['password'] = ConfigPrivate::get('db_password');
        }

        
        foreach (array('type', 'host', 'database', 'port', 'username', 'password', 'driver_options', 'charset', 'collation') as $p) {
            if (!array_key_exists($p, $config)) {
                $config[$p] = null;
            }
        }
        
        // Build dsn from individual components if not defined
        if (!$config['dsn']) {
            if (!$config['type']) {
                $config['type'] = 'pgsql';
            }
            
            $params = array();
            
            if (!$config['host']) {
                throw new DBIConnexionMissingParameterException('host');
            }
            $params[] = 'host='.$config['host'];
            
            if (!$config['database']) {
                throw new DBIConnexionMissingParameterException('database');
            }
            $params[] = 'dbname='.$config['database'];
            
            if ($config['port']) {
                $params[] = 'port='.$config['port'];
            }
            
            $config['dsn'] = $config['type'].':'.implode(';', $params);
        }
        
        // Check that required parameters are not empty
        if (!$config['username']) {
            throw new DBIConnexionMissingParameterException('username');
        }
        if (!$config['password']) {
            throw new DBIConnexionMissingParameterException('password');
        }

        if (!$config['driver_options']) {
            $config['driver_options'] = array();
        }
        
        self::$config = $config;
    }

    /**
     * Reconnect to the database, flushing all cached config
     * parameters first to allow override()s in the config to take effect.
     *
     * You might like to use this method after using
     * Config::localOverride() to change the database connection settings
     * which might be useful if you are testing and want to use another database
     * than the one in the real config settings.
     */
    public static function forceReconnect()
    {
        self::$config = null;
        self::connect(true);
    }

    /**
     * Connect to database
     *
     * @param boolean $force_reconnect
     *
     * @throws DBIConnexionMissingParameterException
     * @throws DBIConnexionException
     */
    private static function connect($force_reconnect = false)
    {
        if (!$force_reconnect && self::$instance) {
            return;
        }
        
        // Close any existing connexion
        self::$instance = null;
        
        if (is_null(self::$config)) {
            self::load();
        }

        $username = self::$config['username'];
        $password = self::$config['password'];

        if( Logger::isUpgradeProcess()) {
            if (array_key_exists('username_admin',self::$config) && self::$config['username_admin']) {
                $username = self::$config['username_admin'];
            }
            if (array_key_exists('password_admin',self::$config) && self::$config['password_admin']) {
                $password = self::$config['password_admin'];
            }
        }
        
        // Try to connect, cast any thrown exception
        try {
            // Connect
            self::$instance = new PDO(
                self::$config['dsn'],
                $username,
                $password,
                self::$config['driver_options']
            );
            
            // Set options : throw if error, do not cast returned values to string, fetch as associative array by default
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            self::$last_request = time();
            
            // db_charset given in config ?
            if (self::$config['charset']) {
                if (self::$config['collation']) {
                    self::prepare('SET NAMES :charset COLLATE :collation')->execute(array(
                        ':charset' => self::$config['charset'],
                        ':collation' => self::$config['collation']
                    ));
                } else {
                    self::prepare('SET NAMES :charset')->execute(array(
                        ':charset' => self::$config['charset']
                    ));
                }
            }
        } catch (Exception $e) {
            throw new DBIConnexionException('DBI connect error : '.$e->getMessage());
        }
    }
    
    /**
     * Ping connection
     */
    private static function ping()
    {
        // Do not ping if last request less than Xmin ago
        $timeout = array_key_exists('timeout', self::$config) ? (int)self::$config['timeout'] : null;
        if (!$timeout) {
            $timeout = 900;
        }
        
        if (self::$instance && (microtime(true) - self::$last_request < $timeout)) {
            return;
        }
        
        if (self::$instance) {
            try {
                self::$instance->query('SELECT 1');
                return;
            } catch (PDOException $e) {
            }
        }
        
        // Ping failed, reconnect
        self::connect(true);
        
        // Only log after as logger may use DBI
        Logger::info('Connection to database was lost, restored');
    }
    
    /**
     * Magic call handler
     *
     * Forwards calls to static methods to existing PDO instance methods
     *
     * @param string $name name of the wanted method
     * @param array $args arguments to forward
     *
     * @throws DBIUsageException
     *
     * @return mixed value returned by PDO call
     */
    public static function __callStatic($name, $args)
    {
        // Connect if not already done
        self::connect();
        
        // Log usual queries
        if (in_array($name, array('prepare', 'query', 'exec'))) {
            Logger::debug('DBI call');
        }
        
        // Does the called method exist ?
        if (!method_exists(self::$instance, $name)) {
            throw new DBIUsageException('Calling unknown DBI method '.$name);
        }
        
        // Try to call, cast any thrown exception
        try {
            self::ping();
            
            $r = call_user_func_array(array(self::$instance, $name), $args);
            
            self::$last_request = time();
            
            // Cast any returned PDOStatment to a DBIStatment so that fetches and such may be logged
            if (is_object($r) && ($r instanceof PDOStatement)) {
                return new DBIStatement($r);
            }
                
            return $r;
        } catch (Exception $e) {
            $dbtype = Config::get('db_type');
            $code = $e->getCode();
            if ($dbtype == 'pgsql') {
                // print_r($e,false);
                if ($code == 42710) {
                    throw new DBIDuplicateException($e->getMessage(), array('name' => $name, 'args' => $args));
                }
            }
            throw new DBIUsageException($e->getMessage(), array('name' => $name, 'args' => $args));
        }
    }
    
    /**
     * Prepare IN query
     *
     * @param string $query
     * @param array $sets pairs of identifiers and values sets or values sets counts
     *
     * @return string
     */
    public static function prepareInQuery($query, $sets)
    {
        foreach ($sets as $key => $values) {
            if (is_array($values)) {
                $values = count($values);
            }
            
            // If there is values replace by fitting amount of OR clauses, set falsy clause otherwise
            if (is_int($values) && $values) {
                $query = preg_replace_callback('`\s+([^\s]+)\s+IN\s+'.$key.'\b`i', function ($m) use ($key, $values) {
                    $cdn = array();
                    for ($i=0; $i<$values; $i++) {
                        $cdn[] = $m[1].' = '.$key.'___'.$i;
                    }
                    
                    return ' ('.implode(' OR ', $cdn).') ';
                }, $query);
            } else {
                $query = preg_replace('`\s+([^\s]+)\s+IN\s+'.$key.'\b`i', ' 1=0', $query);
            }
        }
        
        // Prepare transformed query
        return self::prepare($query);
    }
}

/**
 * Wrapper around PDOStatement for better exception handling
 */
class DBIStatement
{
    /**
     * Real statement
     */
    private $statement = null;
    
    /**
     * Creates statement
     *
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }
    
    /**
     * Call forwarder
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     *
     * @throws DBIUsageException
     */
    public function __call($method, $args)
    {
        // Log execute calls
        if ($method == 'execute') {
            Logger::debug('DBI call');
        }
        
        // Transform any IN subset into serialized OR values
        if ($method == 'execute') {
            foreach ($args[0] as $key => $value) {
                if (is_array($value)) {
                    $values = array_values($value);
                    foreach ($values as $i => $iValue) {
                        $args[0][ $key . '___' . $i ] = $iValue;
                    }

                    unset($args[0][$key]);
                }
            }
        }
        
        // Is the required method valid ?
        if (!method_exists($this->statement, $method)) {
            throw new DBIUsageException('Calling unknown DBIStatement method '.$method);
        }
        
        // Tries to propagate the call, cast any thrown exception
        try {
            return call_user_func_array(array($this->statement, $method), $args);
        } catch (Exception $e) {
            throw new DBIUsageException($e->getMessage(), array('method' => $method, 'args' => $args, 'query' => $this->statement->queryString));
        }
    }
}
