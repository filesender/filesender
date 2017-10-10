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
        
        foreach($definition as $column => $def) {
            if(array_key_exists('autoinc', $def) && $def['autoinc']) {
                self::createSequence($table, $column);
            }
        }
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
     * Create sequence for table column if it does not exist already
     * 
     * @param string $table table name
     * @param string $column column name
     * 
     * @return mixed created sequence name or false if already exists
     */
    private static function createSequence($table, $column) {
        $sequence = self::sequenceExists($table, $column, false);
        if(!$sequence) {
            $sequence = strtolower($table.'_'.$column.'_seq');
            DBI::exec('CREATE SEQUENCE '.$sequence);
        }
        
        DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' SET DEFAULT nextval(\''.$sequence.'\')');
        DBI::exec('ALTER SEQUENCE '.$sequence.' OWNED BY '.$table.'.'.$column);
        
        return $sequence;
    }
    
    /**
     * Check if sequence exists
     * 
     * @param string $table table name
     * @param string $column column name
     * 
     * @return mixed sequence name or false
     */
    private static function sequenceExists($table, $column, $test_ownership = true) {
        if($test_ownership) {
            $s = DBI::prepare('SELECT c.relname AS seq FROM pg_class c JOIN pg_depend d ON d.objid=c.oid AND d.classid=\'pg_class\'::regclass AND d.refclassid=\'pg_class\'::regclass JOIN pg_class t ON t.oid=d.refobjid JOIN pg_attribute a ON a.attrelid=t.oid AND a.attnum=d.refobjsubid WHERE c.relkind=\'S\' and d.deptype=\'a\' AND t.relname=:table AND a.attname=:column');
            $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
        } else {
            $s = DBI::prepare('SELECT c.relname AS seq FROM pg_class c WHERE c.relkind = \'S\' AND c.relname = :sequence');
            $s->execute(array(':sequence' => strtolower($table.'_'.$column.'_seq')));
        }
        
        $r = $s->fetch();
        
        return $r ? $r['seq'] : false;
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
        
        if(array_key_exists('autoinc', $definition) && $definition['autoinc']) {
            self::createSequence($table, $column);
        }
    }

    public static function dropTableSecondaryIndex(   $table, $index ) {
        if(!$logger || !is_callable($logger)) $logger = function() {};
        $query = 'DROP INDEX '.$index;
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
        $s = DBI::prepare('SELECT a.attname, pg_catalog.format_type(a.atttypid, a.atttypmod), a.attnotnull, a.attnum,'
                . ' pg_catalog.pg_get_indexdef(a.attrelid, a.attnum, TRUE) AS indexdef '
                . ' FROM pg_catalog.pg_attribute a '
                . ' WHERE a.attrelid = '
                . ' ( SELECT c.oid FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace '
                . "    WHERE c.relname ~ '^(" . strtolower($index) . ")\$' "
                . '      AND pg_catalog.pg_table_is_visible(c.oid) '
                . '    LIMIT 1 ) '
                . ' AND a.attnum > 0 AND NOT a.attisdropped '
                . 'ORDER BY a.attnum');
        $s->execute(array());

        $existingCols = array();
        foreach($s->fetchAll() as $r) {
            $existingCols[] = $r['attname'];
        }

        rsort($existingCols);
        rsort($expected);
        if( !count($existingCols))
            return DatabaseSecondaryIndexStatuses::NOTFOUND;
            
        return $existingCols != $expected ? DatabaseSecondaryIndexStatuses::INCORRECT_DEFINITION : false;
    }

    private static function quoteString( $v ) {
        return "'".str_replace("'", "\\'", $v)."'";
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
        $s = DBI::prepare('SELECT * FROM information_schema.columns WHERE table_name=:table AND column_name=:column');
        $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
        $column_dfn = $s->fetch();
        
        $non_respected = array();
        
        $typematcher = '';
        $length = null;
        
        // Build type matcher
        switch($definition['type']) {
            case 'int':
            case 'uint':
                $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                if(!$size) $size = 'medium';
                $s2s = array('small' => 'smallint', 'medium' => 'integer', 'big' => 'bigint');
                $typematcher = $s2s[$size];
                break;
            
            case 'string':
                $typematcher = 'character varying';
                $length = (int)$definition['size'];
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
        
        // Check type
        if(!preg_match('`'.$typematcher.'`i', $column_dfn['data_type'])) {
            $logger($column.' type does not match '.$typematcher);
            $non_respected[] = 'type';
        }
        
        // Check length if any
        if(!is_null($length) && ((int)$column_dfn['character_maximum_length'] != $length)) {
            $logger($column.' max length does not match '.$length);
            $non_respected[] = 'type';
        }
        
        // Check default
        if(array_key_exists('default', $definition)) {
            if(is_null($definition['default'])) {
                if(!is_null($column_dfn['column_default'])) {
                    $logger($column.' default is not null');
                    $non_respected[] = 'default';
                }
            }else if(is_bool($definition['default'])) {
                if((bool)$column_dfn['column_default'] != $definition['default']) {
                    $logger($column.' default is not '.($definition['default'] ? '1' : '0'));
                    $non_respected[] = 'default';
                }
            }else if($column_dfn['column_default'] != $definition['default']
            && $column_dfn['column_default'] != self::quoteString($definition['default']).'::character varying') {
                $logger($column.' default is not "'.$definition['default'].'"');
                $non_respected[] = 'default';
            }
        }
        
        // Options defaults
        foreach(array('null', 'primary', 'unique', 'autoinc') as $k) if(!array_key_exists($k, $definition)) $definition[$k] = false;
        
        // Check nullable
        $is_null = (strtolower($column_dfn['is_nullable']) == 'yes');
        if($definition['null'] && !$is_null) {
            $logger($column.' is not nullable');
            $non_respected[] = 'null';
        } else if(!$definition['null'] && $is_null) {
            $logger($column.' should not be nullable');
            $non_respected[] = 'null';
        }
        
        // Check primary
        $is_primary = false;
        $s = DBI::prepare('SELECT pg_attribute.attname FROM pg_attribute JOIN pg_class ON pg_class.oid = pg_attribute.attrelid LEFT JOIN pg_constraint ON pg_constraint.conrelid = pg_class.oid AND pg_attribute.attnum = ANY (pg_constraint.conkey) WHERE pg_class.relkind = \'r\' AND pg_class.relname = :table AND pg_attribute.attname = :column AND pg_constraint.contype = \'p\'');
        $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
        if($s->fetch()) $is_primary = true;
        if($definition['primary'] && !$is_primary) {
            $logger($column.' is not primary');
            $non_respected[] = 'primary';
        } else if(!$definition['primary'] && $is_primary) {
            $logger($column.' should not be primary');
            $non_respected[] = 'primary';
        }
        
        // Check unique
        $is_unique = false;
        $s = DBI::prepare('SELECT pg_attribute.attname FROM pg_attribute JOIN pg_class ON pg_class.oid = pg_attribute.attrelid LEFT JOIN pg_constraint ON pg_constraint.conrelid = pg_class.oid AND pg_attribute.attnum = ANY (pg_constraint.conkey) WHERE pg_class.relkind = \'r\' AND pg_class.relname = :table AND pg_attribute.attname = :column AND pg_constraint.contype = \'u\'');
        $s->execute(array(':table' => strtolower($table), ':column' => strtolower($column)));
        if($s->fetch()) $is_unique = true;
        if($definition['unique'] && !$is_unique) {
            $logger($column.' is not unique');
            $non_respected[] = 'unique';
        } else if(!$definition['unique'] && $is_unique) {
            $logger($column.' should not be unique');
            $non_respected[] = 'unique';
        }
        
        // Check autoinc
        $is_autoinc = self::sequenceExists($table, $column);
        if($definition['autoinc'] && (!$is_autoinc || ($column_dfn['column_default'] != 'nextval(\''.$is_autoinc.'\'::regclass)'))) {
            $logger($column.' is not autoinc');
            $non_respected[] = 'autoinc';
        } else if(!$definition['autoinc'] && $is_autoinc) {
            $logger($column.' should not be autoinc');
            $non_respected[] = 'autoinc';
        }
        
        // Return any error
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
        if(in_array('type', $problems)) {
            // Cahnge data type
            $type = '';
            switch($definition['type']) {
                case 'int':
                case 'uint':
                    $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                    if(!$size) $size = 'medium';
                    $s2s = array('small' => 'smallint', 'medium' => 'integer', 'big' => 'bigint');
                    $type .= $s2s[$size];
                    break;
                
                case 'string':
                    $type .= 'character varying('.$definition['size'].')';
                    break;
                
                case 'bool':
                    $type .= 'boolean';
                    break;
                
                case 'text':
                    $type .= 'text';
                    break;
                
                case 'date':
                    $type .= 'date';
                    break;
                
                case 'datetime':
                    $type .= 'timestamp';
                    break;
                
                case 'time':
                    $type .= 'time';
                    break;
            }
            
            if($type) DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' TYPE '.$type);
        }
        
        // Options defaults
        foreach(array('null', 'primary', 'unique', 'autoinc') as $k) if(!array_key_exists($k, $definition)) $definition[$k] = false;
        
        // Change nullable
        if(in_array('null', $problems)) {
            if($definition['null']) {
                DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' DROP NOT NULL');
            } else {
                DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' SET NOT NULL');
            }
        }
        
        // Change default
        if(in_array('default', $problems)) {
            if(array_key_exists('default', $definition)) {
                if(is_null($definition['default'])) {
                    DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' SET DEFAULT NULL');
                } else if(is_bool($definition['default'])) {
                    DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' SET DEFAULT '.($definition['default'] ? '1' : '0'));
                } else {
                    DBI::prepare('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' SET DEFAULT :default')-execute(array(':default' => $definition['default']));
                }
            } else {
                DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' DROP DEFAULT');
            }
        }
        
        // Change primary
        if(in_array('primary', $problems)) {
            if($definition['primary']) {
                DBI::exec('ALTER TABLE '.$table.' ADD CONSTRAINT primary_'.$column.' PRIMARY KEY ('.$column.')');
            } else {
                DBI::exec('ALTER TABLE '.$table.' DROP CONSTRAINT primary_'.$column);
            }
        }
        
        // Change unique
        if(in_array('unique', $problems)) {
            if($definition['unique']) {
                DBI::exec('ALTER TABLE '.$table.' ADD CONSTRAINT unique_'.$column.' UNIQUE ('.$column.')');
            } else {
                DBI::exec('ALTER TABLE '.$table.' DROP CONSTRAINT unique_'.$column);
            }
        }
        
        // Change autoinc
        if(in_array('autoinc', $problems)) {
            if($definition['autoinc']) {
                self::createSequence($table, $column);
            } else {
                DBI::exec('ALTER TABLE '.$table.' ALTER COLUMN '.$column.' DROP DEFAULT'); // Should we drop the sequence as well ?
            }
        }
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
        
        // Build type part
        switch($definition['type']) {
            case 'int':
            case 'uint':
                $size = array_key_exists('size', $definition) ? $definition['size'] : 'medium';
                if(!$size) $size = 'medium';
                $s2s = array('small' => 'smallint', 'medium' => 'integer', 'big' => 'bigint');
                $sql .= $s2s[$size];
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
        
        // Add nullable
        if(!array_key_exists('null', $definition) || !$definition['null']) $sql .= ' NOT NULL';
        
        // Add default
        if(array_key_exists('default', $definition)) {
            $sql .= ' DEFAULT ';
            $default = $definition['default'];
            
            if(is_null($default)) {
                $sql .= 'NULL';
            }else if(is_bool($default)) {
                $sql .= $default ? '1' : '0';
            }else if(is_numeric($default) && in_array($definition['type'], array('int', 'uint'))) {
                $sql .= $default;
            }else $sql .= "'".str_replace("'", "\\'", $default)."'";
        }
        
        // Add options
        if(array_key_exists('unique', $definition) && $definition['unique']) $sql .= ' UNIQUE';
        if(array_key_exists('primary', $definition) && $definition['primary']) $sql .= ' PRIMARY KEY';
        
        // Return statment
        return $sql;
    }
}
