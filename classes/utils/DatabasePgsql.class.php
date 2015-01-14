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
class DatabasePgsql {
    /**
     * Check if a table exists
     */
    public static function tableExists($table) {
        $s = DBI::prepare('SELECT * FROM pg_tables WHERE tablename=:table');
        $s->execute(array(':table' => strtolower($table)));
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
        $s = DBI::query('SELECT column_name FROM information_schema.columns WHERE table_name=\''.strtolower($table).'\'');
        $columns = array();
        foreach($s->fetchAll() as $r) $columns[] = $r['column_name'];
        return $columns;
    }
    
    /**
     * Table columns removing.
     * 
     * @param string $table table name
     * @param string $column column name
     */
    public static function removeTableColumn($table, $column) {
        $query = 'ALTER TABLE '.$table.' DROP COLUMN '.$column;
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
        $s = DBI::prepare('SELECT * FROM information_schema.columns WHERE table_name=:table AND column_name=:column');
        $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
        $column = $s->fetch();
        
        $typematcher = '';
        
        switch($definition['type']) {
            case 'int':
            case 'uint':
                $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                if(!$size) $size = 'medium';
                $s2s = array('small' => 'smallint', 'medium' => 'integer', 'big' => 'bigint');
                $typematcher = $s2s[$size];
                if(array_key_exists('primary', $definition) && $definition['primary'])
                    $typematcher = 'serial';
                break;
            
            case 'string':
                $typematcher = 'character varying\('.$definition['size'].'\)';
                break;
            
            case 'bool':
                $typematcher = 'boolean';
                break;
            
            case 'text':
                $typematcher = 'text';
                break;
            
            case 'date':
                $typematcher = 'date';
                break;
            
            case 'datetime':
                $typematcher = 'timestamp';
                break;
            
            case 'time':
                $typematcher = 'time';
                break;
        }
        
        if(!preg_match('`'.$typematcher.'`i', $column['data_type'])) {
            if($logger && is_callable($logger)) $logger($column['column_name'].' type does not match '.$typematcher);
            return false;
        }
        
        $null = 'no';
        if(array_key_exists('null', $definition) && $definition['null']) $null = 'yes';
        if($column['is_nullable'] != $null) {
            if($logger && is_callable($logger)) $logger($column['column_name'].' is_nullable is not '.$null);
            return false;
        }
        
        if(array_key_exists('default', $definition)) {
            if(is_null($definition['default'])) {
                if(!is_null($column['column_default'])) {
                    if($logger && is_callable($logger)) $logger($column['column_name'].' default is not null');
                    return false;
                }
            }else if(is_bool($definition['default'])) {
                if((bool)$column['column_default'] != $definition['default']) {
                    if($logger && is_callable($logger)) $logger($column['column_name'].' default is not '.($definition['default'] ? '1' : '0'));
                    return false;
                }
            }else if($column['column_default'] != $definition['default']) {
                if($logger && is_callable($logger)) $logger($column['column_name'].' default is not "'.$definition['default'].'"');
                return false;
            }
        }
        
        if(array_key_exists('primary', $definition) && $definition['primary']) {
            $s = DBI::prepare('SELECT pg_attribute.attname FROM pg_attribute JOIN pg_class ON pg_class.oid = pg_attribute.attrelid LEFT JOIN pg_constraint ON pg_constraint.conrelid = pg_class.oid AND pg_attribute.attnum = ANY (pg_constraint.conkey) WHERE pg_class.relkind = "r"::char AND pg_class.relname = :table AND pg_attribute.attname = :column AND pg_constraint.contype = \'p\'');
            $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
            if(!$s->fetch()) {
                if($logger && is_callable($logger)) $logger($column['column_name'].' key is not PRI');
                return false;
            }
        }else if(array_key_exists('unique', $definition) && $definition['unique']) {
            $s = DBI::prepare('SELECT pg_attribute.attname FROM pg_attribute JOIN pg_class ON pg_class.oid = pg_attribute.attrelid LEFT JOIN pg_constraint ON pg_constraint.conrelid = pg_class.oid AND pg_attribute.attnum = ANY (pg_constraint.conkey) WHERE pg_class.relkind = "r"::char AND pg_class.relname = :table AND pg_attribute.attname = :column AND pg_constraint.contype = \'u\'');
            $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
            if(!$s->fetch()) {
                if($logger && is_callable($logger)) $logger($column['column_name'].' key is not UNI');
                return false;
            }
        }
        
        if(array_key_exists('autoinc', $definition) && $definition['autoinc']) {
            if(!preg_match('`serial`i', $column['data_type'])) {
                if($logger && is_callable($logger)) $logger($column['column_name'].' extra does not contain auto_increment');
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
        $query = 'ALTER TABLE '.$table.' ALTER COLUMN '.$column.' TYPE  '.self::columnDefinition($definition);
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
        $sql = '';
        
        switch($definition['type']) {
            case 'int':
            case 'uint':
                if(array_key_exists('primary', $definition) && $definition['primary']) {
                    $sql .= 'serial';
                } else {
                    $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                    if(!$size) $size = 'medium';
                    $s2s = array('small' => 'smallint', 'medium' => 'integer', 'big' => 'bigint');
                    $sql .= $s2s[$size];
                }
                break;
            
            case 'string':
                $sql .= 'character varying('.$definition['size'].')';
                break;
            
            case 'bool':
                $sql .= 'boolean';
                break;
            
            case 'text':
                $sql .= 'text';
                break;
            
            case 'date':
                $sql .= 'date';
                break;
            
            case 'datetime':
                $sql .= 'timestamp';
                break;
            
            case 'time':
                $sql .= 'time';
                break;
        }
        
        if(!array_key_exists('null', $definition) || !$definition['null']) $sql .= ' NOT NULL';
        
        if(array_key_exists('default', $definition)) {
            $sql .= ' DEFAULT ';
            $default = $definition['default'];
            
            if(is_null($default)) {
                $sql .= 'NULL';
            }else if(is_bool($default)) {
                $sql .= $default ? '1' : '0';
            }else if(is_numeric($default) && in_array($definition['type'], array('int', 'uint'))) {
                $sql .= $default;
            }else $sql .= '"'.str_replace('"', '\\"', $default).'"';
        }
        
        if(array_key_exists('unique', $definition) && $definition['unique']) $sql .= ' UNIQUE';
        if(array_key_exists('primary', $definition) && $definition['primary']) $sql .= ' PRIMARY KEY';
        
        return $sql;
    }
}
