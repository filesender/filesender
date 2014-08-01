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
 * Database managing
 */
class Database {
    /**
     * Cache if delegation class was loaded.
     */
    private static $class = null;
    
    /**
     * Delegates table exists check.
     * 
     * @param string $table table name
     * 
     * @return bool
     */
    public static function tableExists($table) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::tableExists', $table);
    }
    
    /**
     * Delegates table columns getter.
     * 
     * @param string $table table name
     * 
     * @return array of column names
     */
    public static function getTableColumns($table) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::getTableColumns', $table);
    }
    
    /**
     * Delegates table columns removing.
     * 
     * @param string $table table name
     * @param string $column column name
     */
    public static function removeTableColumn($table, $column) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::removeTableColumn', $table, $column);
    }
    
    /**
     * Delegates table columns creation.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     */
    public static function createTableColumn($table, $column, $definition) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::createTableColumn', $table, $column, $definition);
    }
    
    /**
     * Delegates table columns format checking.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     * 
     * @return bool
     */
    public static function checkTableColumnFormat($table, $column, $definition, $logger = null) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::checkTableColumnFormat', $table, $column, $definition, $logger);
    }
    
    /**
     * Delegates table columns format update.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     */
    public static function updateTableColumnFormat($table, $column, $definition) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::updateTableColumnFormat', $table, $column, $definition);
    }
    
    /**
     * Create a table
     * 
     * @param string $table the table name
     * @param array $definition dataMap entry
     * 
     */
    public static function createTable($table, $definition) {
        $class = self::getDelegationClass();
        
        return call_user_func($class.'::createTable', $table, $definition);
    }
    
    /**
     * Get selected database delegation class
     * 
     * @return string delegation class name
     */
    private static function getDelegationClass() {
        if(is_null(self::$class)) {
            $type = Config::get('db_type');
            
            if(!$type) throw new ConfigBadParameterException('db_type');
            $class = 'Database'.ucfirst($type);
            $file = FILESENDER_BASE.'/classes/utils/'.$class.'.class.php';
            
            if(!file_exists($file)) throw new CoreFileNotFoundException($file);
            
            self::$class = $class;
        }
        
        return self::$class;
    }
}
