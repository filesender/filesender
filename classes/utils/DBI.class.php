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
if(!defined('FILESENDER_BASE')) die('Missing environment');

/**
 * Database access abstraction class
 * 
 * Handles connexion setup, provides PDO instance methods shorthands and easing methods
 */
class DBI {
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
    private static function connect() {
        if(!is_null(self::$instance)) return;
        
        // Get config, check mandatory parameters
        $config = Config::get('db_*');
        $config['dsn'] = Config::get('dsn');
        foreach(array('type', 'host', 'database', 'port', 'username', 'password', 'driver_options', 'charset', 'collation') as $p) {
            if(!array_key_exists($p, $config)) $config[$p] = null;
        }
        
        if(!$config['dsn']) {
            if(!$config['type']) $config['type'] = 'pgsql';
            
            $params = array();
            
            if(!$config['host']) throw new DBIConnexionMissingParameterException('host');
            $params[] = 'host='.$config['host'];
            
            if(!$config['database']) throw new DBIConnexionMissingParameterException('database');
            $params[] = 'dbname='.$config['database'];
            
            if($config['port']) $params[] = 'port='.$config['port'];
            
            $config['dsn'] = $config['type'].':'.implode(';', $params);
        }
        
        if(!$config['username']) throw new DBIConnexionMissingParameterException('username');
        if(!$config['password']) throw new DBIConnexionMissingParameterException('password');
        
        if(!$config['driver_options']) $config['driver_options'] = array();
        
        try {
            self::$instance = new PDO($config['dsn'], $config['username'], $config['password'], $config['driver_options']);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // db_charset given in config ?
            if($config['charset']) {
                if($config['collation']) {
                    self::prepare('SET NAMES :charset COLLATE :collation')->execute(array(':charset' => $config['charset'], ':collation' => $config['collation']));
                }else{
                    self::prepare('SET NAMES :charset')->execute(array(':charset' => $config['charset']));
                }
            }
        } catch(Exception $e) {
            throw new DBIConnexionException('DBI connect error : '.$e->getMessage());
        }
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
    public static function __callStatic($name, $args) {
        self::connect();
        if(!method_exists(self::$instance, $name)) throw new DBIUsageException('Calling unknown DBI method '.$name);
        try {
            return call_user_func_array(array(self::$instance, $name), $args);
        } catch(Exception $e) {
            throw new DBIUsageException($e->getMessage(), array('name' => $name, 'args' => $args));
        }
    }
}
