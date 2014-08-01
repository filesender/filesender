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
class DatabaseMysql {
    /**
     * Check if a table exists
     */
    public static function tableExists($table) {
        $s = DBI::prepare('SHOW TABLES LIKE :table');
        $s->execute(array(':table' => $table));
        return (bool)$s->fetch();
    }
    
    /**
     * Create a table
     * 
     * @param string $table the table name
     * @param array $definition dataMap entry
     * 
     */
    public static function createTable($table, $definition) {
        $columns = array();
        
        foreach($definition as $column => $def) {
            $columns[] = $column.' '.self::columnDefinition($def);
        }
        $query = 'CREATE TABLE '.$table.' ('.implode(', ', $columns).')';
        
        DBI::exec($query);
    }
    
    /**
     * Table columns getter.
     * 
     * @param string $table table name
     * 
     * @return array of column names
     */
    public static function getTableColumns($table) {
        $s = DBI::query('SHOW COLUMNS FROM '.$table);
        $columns = array();
        foreach($s->fetchAll() as $r) $columns[] = $r['Field'];
        return $columns;
    }
    
    /**
     * Table columns removing.
     * 
     * @param string $table table name
     * @param string $column column name
     */
    public static function removeTableColumn($table, $column) {
        $query = 'ALTER TABLE '.$table.' DROP '.$column;
        DBI::exec($query);
    }
    
    /**
     * Table columns creation.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     */
    public static function createTableColumn($table, $column, $definition) {
        $query = 'ALTER TABLE '.$table.' ADD '.self::columnDefinition($definition);
        DBI::exec($query);
    }
    
    /**
     * Table columns format checking.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     * 
     * @return bool
     */
    public static function checkTableColumnFormat($table, $column, $definition, $logger = null) {
        $s = DBI::prepare('SHOW COLUMNS FROM '.$table.' LIKE :column');
        $s->execute(array(':column' => $column));
        $column = $s->fetch();
        
        $typematcher = '';
        
        switch($definition['type']) {
            case 'int':
            case 'uint':
                $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                if(!$size) $size = 'medium';
                $typematcher = $size.'int(?:\([0-9]+\))';
                if($definition['type'] == 'uint') $typematcher .= ' unsigned';
                break;
            
            case 'string':
                $typematcher = 'varchar\('.$definition['size'].'\)';
                break;
            
            case 'bool':
                $typematcher = 'tinyint\([0-9]+\) unsigned';
                break;
            
            case 'enum':
                $typematcher = 'enum('.implode(',', $definition['values']).')';
                break;
            
            case 'text':
                $typematcher = 'text';
                break;
            
            case 'date':
                $typematcher = 'date';
                break;
            
            case 'datetime':
                $typematcher = 'datetime';
                break;
            
            case 'time':
                $typematcher = 'time';
                break;
        }
        
        if(!preg_match('`'.$typematcher.'`i', $column['Type'])) {
            if($logger && is_callable($logger)) $logger($column['Field'].' type does not match '.$typematcher);
            return false;
        }
        
        $null = 'NO';
        if(array_key_exists('null', $definition) && $definition['null']) $null = 'YES';
        if($column['Null'] != $null) {
            if($logger && is_callable($logger)) $logger($column['Field'].' null is not '.$null);
            return false;
        }
        
        if(array_key_exists('default', $definition)) {
            if(is_null($definition['default'])) {
                if(!is_null($column['Default'])) {
                    if($logger && is_callable($logger)) $logger($column['Field'].' default is not null');
                    return false;
                }
            }else if(is_bool($definition['default'])) {
                if((bool)$column['Default'] != $definition['default']) {
                    if($logger && is_callable($logger)) $logger($column['Field'].' default is not '.($definition['default'] ? '1' : '0'));
                    return false;
                }
            }else if($column['Default'] != $definition['default']) {
                if($logger && is_callable($logger)) $logger($column['Field'].' default is not "'.$definition['default'].'"');
                return false;
            }
        }
        
        if(array_key_exists('primary', $definition) && $definition['primary']) {
            if($column['Key'] != 'PRI') {
                if($logger && is_callable($logger)) $logger($column['Field'].' key is not PRI');
                return false;
            }
        }else if(array_key_exists('unique', $definition) && $definition['unique']) {
            if($column['Key'] != 'UNI') {
                if($logger && is_callable($logger)) $logger($column['Field'].' key is not UNI');
                return false;
            }
        }
        
        if(array_key_exists('autoinc', $definition) && $definition['autoinc']) {
            if(!preg_match('`auto_increment`', $column['Extra'])) {
                if($logger && is_callable($logger)) $logger($column['Field'].' extra does not contain auto_increment');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Table columns format update.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     */
    public static function updateTableColumnFormat($table, $column, $definition) {
        $query = 'ALTER TABLE '.$table.' MODIFY '.$column.' '.self::columnDefinition($definition);
        DBI::exec($query);
    }
    
    /**
     * Get column definition
     * 
     * @param array $definition dataMap entry
     * 
     * @return string Mysql definition
     */
    private static function columnDefinition($definition) {
        $mysql = '';
        
        switch($definition['type']) {
            case 'int':
            case 'uint':
                $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                if(!$size) $size = 'medium';
                $mysql = strtoupper($size).'INT';
                if($definition['type'] == 'uint') $mysql .= ' UNSIGNED';
                break;
            
            case 'string':
                $size = array_key_exists('size', $definition) ? $definition['size'] : '255';
                $mysql = 'VARCHAR('.$size.')';
                break;
            
            case 'bool':
                $mysql = 'TINYINT UNSIGNED';
                break;
            
            case 'enum':
                $mysql = 'ENUM('.implode(',', $definition['values']).')';
                break;
            
            case 'text':
                $mysql = 'TEXT';
                break;
            
            case 'date':
                $mysql = 'DATE';
                break;
            
            case 'datetime':
                $mysql = 'DATETIME';
                break;
            
            case 'time':
                $mysql = 'TIME';
                break;
        }
        
        $null = 'NOT NULL';
        if(array_key_exists('null', $definition) && $definition['null']) $null = 'NULL';
        $mysql .= ' '.$null;
        
        if(array_key_exists('default', $definition)) {
            $mysql .= ' DEFAULT ';
            $default = $definition['default'];
            
            if(is_null($default)) {
                $mysql .= 'NULL';
            }else if(is_bool($default)) {
                $mysql .= $default ? '1' : '0';
            }else if(is_numeric($default) && in_array($definition['type'], array('int', 'uint'))) {
                $mysql .= $default;
            }else $mysql .= '"'.str_replace('"', '\\"', $default).'"';
        }
        
        if(array_key_exists('autoinc', $definition) && $definition['autoinc']) $mysql .= ' AUTO_INCREMENT';
        if(array_key_exists('unique', $definition) && $definition['unique']) $mysql .= ' UNIQUE KEY';
        if(array_key_exists('primary', $definition) && $definition['primary']) $mysql .= ' PRIMARY KEY';
        
        return $mysql;
    }
}
