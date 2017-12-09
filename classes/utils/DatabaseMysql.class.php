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
        $query = 'ALTER TABLE '.$table.' ADD '.$column.' '.self::columnDefinition($definition);
        DBI::exec($query);
    }


    public static function dropTableSecondaryIndex(   $table, $index ) {
        if(!$logger || !is_callable($logger)) $logger = function() {};
        $query = 'DROP INDEX '.$index.' on '.$table.'';
        DBI::exec($query);
    }
    public static function createTableSecondaryIndex( $table, $index, $definition ) {
        if(!$logger || !is_callable($logger)) $logger = function() {};

        $coldefs = '';
        foreach( $definition as $dk => $dm ) {
            if( $coldefs != '' )
                $coldefs .= ',';
            $coldefs .= $dk;
        }
        $query = 'CREATE INDEX '.$index.' on '.$table.' (' . $coldefs . ')';
        DBI::exec($query);
    }

    /**
     * Table columns format checking.
     * 
     * @param string $table table name
     * @param string $index index name
     * @param string $definition index columns definition
     * 
     * @return string reason if a problem or false if no problems
     */
    public static function checkTableSecondaryIndexFormat($table, $index, $definition, $logger = null) {
        if(!$logger || !is_callable($logger)) $logger = function() {};

        $expected = array();
        foreach( $definition as $dk => $dm ) {
            $expected[] = $dk;
        }

        // Get current definition
        $s = DBI::prepare('SHOW INDEX FROM '.$table.'');
        $s->execute(array(':key_name' => $index));

        $existingCols = array();
        foreach($s->fetchAll() as $r) {
            if( $r['Key_name'] == $index) {
                $existingCols[] = $r['Column_name'];
            }
        }

        rsort($existingCols);
        rsort($expected);
        if( !count($existingCols))
            return DatabaseSecondaryIndexStatuses::NOTFOUND;
            
        return $existingCols != $expected ? DatabaseSecondaryIndexStatuses::INCORRECT_DEFINITION : false;
    }

    /**
     * Table columns format checking.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param string $definition column definition
     * 
     * @return array of non respected options or false if no problems
     */
    public static function checkTableColumnFormat($table, $column, $definition, $logger = null) {
        if(!$logger || !is_callable($logger)) $logger = function() {};
        
        // Get current definition
        $s = DBI::prepare('SHOW COLUMNS FROM '.$table.' LIKE :column');
        $s->execute(array(':column' => $column));
        $column_dfn = $s->fetch();
        
        $non_respected = array();
        $typematcher = '';
        
        // Build type matcher
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
        
        // Check type
        if(!preg_match('`'.$typematcher.'`i', $column_dfn['Type'])) {
            $logger($column.' type does not match '.$typematcher);
            $non_respected[] = 'type';
        }
        
        // Check default
        if(array_key_exists('default', $definition)) {
            if(is_null($definition['default'])) {
                if(!is_null($column_dfn['Default'])) {
                    $logger($column.' default is not null');
                    $non_respected[] = 'default';
                }
            }else if(is_bool($definition['default'])) {
                if((bool)$column_dfn['Default'] != $definition['default']) {
                    $logger($column.' default is not '.($definition['default'] ? '1' : '0'));
                    $non_respected[] = 'default';
                }
            }else if($column_dfn['Default'] != $definition['default']) {
                $logger($column.' default is not "'.$definition['default'].'"');
                $non_respected[] = 'default';
            }
        }
        
        // Options defaults
        foreach(array('null', 'primary', 'unique', 'autoinc') as $k) if(!array_key_exists($k, $definition)) $definition[$k] = false;
        
        // Check nullable
        $is_null = ($column_dfn['Null'] == 'YES');
        if($definition['null'] && !$is_null) {
            $logger($column.' is not nullable');
            $non_respected[] = 'null';
        } else if(!$definition['null'] && $is_null) {
            $logger($column.' should not be nullable');
            $non_respected[] = 'null';
        }
        
        // Check primary
        $is_primary = ($column_dfn['Key'] == 'PRI');
        if($definition['primary'] && !$is_primary) {
            $logger($column.' is not primary');
            $non_respected[] = 'primary';
        } else if(!$definition['primary'] && $is_primary) {
            $logger($column.' should not be primary');
            $non_respected[] = 'primary';
        }
        
        // Check unique
        $is_unique = ($column_dfn['Key'] == 'UNI');
        if($definition['unique'] && !$is_unique) {
            $logger($column.' is not unique');
            $non_respected[] = 'unique';
        } else if(!$definition['unique'] && $is_unique) {
            $logger($column.' should not be unique');
            $non_respected[] = 'unique';
        }
        
        // Check autoinc
        $is_autoinc = preg_match('`auto_increment`', $column_dfn['Extra']);
        if($definition['autoinc'] && !$is_autoinc) {
            $logger($column.' is not autoinc');
            $non_respected[] = 'autoinc';
        } else if(!$definition['autoinc'] && $is_autoinc) {
            $logger($column.' should not be autoinc');
            $non_respected[] = 'autoinc';
        }
        
        // Return any errors
        return count($non_respected) ? $non_respected : false;
    }
    
    /**
     * Table columns format update.
     * 
     * @param string $table table name
     * @param string $column column name
     * @param array $definition column definition
     * @param array $problems problematic options
     */
    public static function updateTableColumnFormat($table, $column, $definition, $problems) {
        $localdef = $definition;
        if(array_key_exists('primary', $localdef) && $localdef['primary'])
            $localdef['primary'] = false;
        $query = 'ALTER TABLE '.$table.' MODIFY '.$column.' '.self::columnDefinition($localdef);
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
        
        // Build type part
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
        
        // Add nullable
        $null = 'NOT NULL';
        if(array_key_exists('null', $definition) && $definition['null']) $null = 'NULL';
        $mysql .= ' '.$null;
        
        // Add default
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
        
        // Add options
        if(array_key_exists('autoinc', $definition) && $definition['autoinc']) $mysql .= ' AUTO_INCREMENT';
        if(array_key_exists('unique', $definition) && $definition['unique']) $mysql .= ' UNIQUE KEY';
        if(array_key_exists('primary', $definition) && $definition['primary']) $mysql .= ' PRIMARY KEY';
        
        // Return statment
        return $mysql;
    }
}
