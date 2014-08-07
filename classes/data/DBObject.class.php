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
 * Base class for database stored objects
 */
class DBObject {
    /**
     * Instances cache
     */
    protected static $objectCache = array();
    
    /**
     * Defines data in database
     * 
     * Used by fromDBData / toDBData methods
     * Can be used to generate database creation queries
     * 
     * Associative array of <field> => <field_def>
     * 
     * <field_def> associative array of field definition entries in :
     *   - type : int, uint, string, bool, enum, text, date, datetime, time
     *   - size (for int types) : tiny, medium, big (defaults to medium)
     *   - size (for string types) : string length
     *   - values (for enum types) : array of possible values
     *   - null : bool indicating if field can be null
     *   - primary : bool indicating if field is primary key
     *   - autoinc : bool indicating if field is auto-incremented
     *   - unique : bool indicating if field is unique or string pointing unicity column set
     *   - default : default value for this field
     */
    protected static $dataMap = array();
    
    /**
     * DataMap getter
     * 
     * @return array the class dataMap
     */
    public static function getDataMap() {
        return static::$dataMap;
    }
    
    /**
     * Check if object is cached
     * 
     * @param string $class class name
     * @param mixed $id unique identifier
     * 
     * @return bool
     */
    public static function existsInCache($class, $id) {
        return array_key_exists($class, self::$objectCache) && array_key_exists($id, self::$objectCache[$class]);
    }
    
    /**
     * Remove instance or whole class from cache or wipe cache out
     * 
     * If $class and $id are given only instance is removed
     * If only $class is given all instances of the class are removed
     * If none of $class and $id are given all instances of all classes are removed
     * 
     * @param string $class class name
     * @param mixed $id unique identifier
     * 
     * @return bool tells if instance/class was registered
     */
    public static function purgeCache($class = null, $id = null) {
        if($class) {
            if(!array_key_exists($class, self::$objectCache)) return false;
            
            if($id) {
                if(!array_key_exists($id, self::$objectCache[$class])) return false;
                unset(self::$objectCache[$class][$id]);
            }else self::$objectCache[$class] = array();
        }else self::$objectCache = array();
        
        return true;
    }
    
    /**
     * Cached getter relying on id
     * 
     * Only creates the object if it was not cached before, calls constructor with id otherwise
     * 
     * @param mixed $id primary key of the object
     * 
     * @return object instance
     */
    public static function fromId($id) {
        $class = get_called_class();
        if(!array_key_exists($class, self::$objectCache)) self::$objectCache[$class] = array();
        if(array_key_exists($id, self::$objectCache[$class])) return self::$objectCache[$class][$id];
        
        $object = new static($id);
        self::$objectCache[$class][$id] = $object;
        return $object;
    }
    
    /**
     * Cached getter relying on data
     * 
     * Only creates the object if it was not cached before, calls constructor with data otherwise
     * then updates object properties
     * 
     * @param mixed $id primary key of the object
     * @param array $data data to create the object from instead of loading it (used by "get all" queries)
     * 
     * @return object instance
     */
    public static function fromData($id, $data = null, $transforms = array()) {
        $class = get_called_class();
        if(!array_key_exists($class, self::$objectCache)) self::$objectCache[$class] = array();
        $object = array_key_exists($id, self::$objectCache[$class]) ? self::$objectCache[$class][$id] : new static(null, $data);
        
        $object->fillFromDBData($data, $transforms);
        
        self::$objectCache[$class][$id] = $object;
        return $object;
    }
    
    /**
     * Default creator
     * 
     * Creates empty object
     * 
     * @return object instance
     */
    public static function create() {
        return new static();
    }
    
    /**
     * Hydrant
     * 
     * Fill out object properties from database data, converting types on the fly, optionnaly running further transforms.
     * 
     * Transforms (<transform>) can be a string to change field_name or a callable to be called with value as first argument
     * and returning transformed value, or an array of both, or falsy (false, 0, null) to skip field despite dataMap.
     * 
     * @param mixed $data associative array or stdClass instance of data from the database
     * @param array $transforms associative array of <field_name> => <transform> (optionnal)
     */
    protected function fillFromDBData($data, $transforms = array()) {
        if(!is_array($data)) $data = (array)$data;
        
        // Iterate over data
        foreach($data as $field_name => $value) {
            if(!array_key_exists($field_name, static::$dataMap)) continue; // Ignore non-mapped data
            
            // Basic types transformations/casting
            switch(static::$dataMap[$field_name]['type']) {
                case 'int':
                case 'uint':
                    $value = (int)$value;
                    break;
                
                case 'float':
                    $value = (float)$value;
                    break;
                
                case 'datetime':
                case 'date':
                    $value = (int)strtotime($value); // UNIX timestamp
                    break;
                
                case 'time':
                    $value = (int)(strtotime($value) % (24 * 3600)); // Offset since 0h00
                    break;
                
                case 'bool':
                    $value = (bool)$value;
                    break;
            }
            
            if(array_key_exists('transform', static::$dataMap[$field_name])) switch(static::$dataMap[$field_name]['transform']) {
                case 'json' :
                    $value = json_decode($value);
                    break;
            }
            
            // Do we asked for further transformations ?
            if(array_key_exists($field_name, $transforms)) {
                if(is_string($transforms[$field_name]) || is_callable($transforms[$field_name])) {
                    $transforms[$field_name] = array($transforms[$field_name]);
                }
                if(is_array($transforms[$field_name])) foreach($transforms[$field_name] as $transform) {
                    if(is_string($transform)) $field_name = $transform; // Key change
                    if(is_callable($transform))  $value = $transform($value); // Value transformation
                }else if(!$transform[$field_name]) continue; // Null/false transform skips entry
            }
            if(property_exists($this, $field_name)) $this->$field_name = $value;
        }
    }
    
    /**
     * Turns object into a database compliant data set using dataMap
     * 
     * Fill out object properties from database data, converting types on the fly, optionnaly running further transforms.
     * 
     * Transforms (<transform>) can be a string to change field_name or a callable to be called with value as first argument
     * and returning transformed value, or an array of both, or falsy (false, 0, null) to skip field despite dataMap.
     * 
     * @param array $transforms set of <field_name> => <transform> (optionnal)
     * 
     * @return array database ready data
     */
    public function toDBData($transforms = array()) {
        $field_names = array_keys(static::$dataMap);
        
        $data = array();
        
        // Iterate over keys
        foreach($field_names as $field_name) {
            $property_name = $field_name;
            $value_transform = null;
            
            // Does the value need transformation ?
            if(array_key_exists($field_name, $transforms)) {
                if(is_string($transforms[$field_name]) || is_callable($transforms[$field_name])) {
                    $transforms[$field_name] = array($transforms[$field_name]);
                }
                if(is_array($transforms[$field_name])) foreach($transforms[$field_name] as $transform) {
                    if(is_string($transform)) $property_name = $transform; // Key change
                    if(is_callable($transform))  $value_transform = $transform; // Value transformation
                }
            }
            
            $value = $this->$property_name;
            
            // Does the value need transformation ?
            if($value_transform) {
                $value = $value_transform($value);
            }else{
                if(array_key_exists('transform', static::$dataMap[$field_name])) switch(static::$dataMap[$field_name]['transform']) {
                    case 'json' :
                        $value = json_encode($value);
                        break;
                }
                
                switch(static::$dataMap[$field_name]['type']) { // Basic types transformations/casting
                    case 'datetime':
                        if(!is_null($value)) $value = date('Y-m-d H:i:s', $value); // UNIX timestamp
                        break;
                    
                    case 'date':
                        if(!is_null($value)) $value = date('Y-m-d', $value); // UNIX timestamp
                        //$value = new Date($value); // Turn into date object which has getters for formatted version
                        break;
                    
                    case 'time':
                        if(!is_null($value)) $value = date('H:i:s', $value); // Offset since 0h00
                        break;
                    
                    case 'bool':
                        $value = $value ? '1' : '0';
                        break;
                }
            }
            
            $data[$field_name] = $value;
        }
        
        return $data;
    }
    
    /**
     * Get database table name
     * 
     * Database table has the same name as the class, except it plural (class User => table Users).
     * 
     * @return string table name
     */
    public static function getDBTable() {
        $name = get_called_class().'s';
        
        if(Config::exists('db_table_prefix')) $name = Config::get('db_table_prefix').$name;
        
        return $name;
    }
    
    /**
     * Insert object as new record
     * 
     * @param array $data database compliant data such as returned by toDBData
     */
    public static function insertRecord($data) {
        $table = static::getDBTable();
        
        $values = array();
        foreach($data as $field_name => $value) $values[':'.$field_name] = $value;
        $s = DBI::prepare('INSERT INTO '.$table.'('.implode(', ', array_keys($data)).') VALUES(:'.implode(', :', array_keys($data)).')');
        $s->execute($values);
    }
    
    /**
     * Update object record
     * 
     * @param array $data database compliant data such as returned by toDBData
     * @param string $key_name field_name to use as primary key (string or array of strings)
     * @param string $where where clause extension (optionnal)
     */
    public static function updateRecord($data, $key_name, $where = null) {
        $table = static::getDBTable();
        
        $placeholders = array();
        $values = array();
        
        $key_names = is_array($key_name) ? $key_name : array($key_name);
        $key_names = array_filter($key_names);
        
        foreach($data as $field_name => $value) {
            if(!in_array($field_name, $key_names)) $placeholders[] = $field_name.' = :'.$field_name;
            $values[':'.$field_name] = $value;
        }
        
        $where_parts = array();
        foreach($key_names as $key_name) $where_parts[] = $key_name.' = :'.$key_name;
        if($where) $where_parts[] = $where;
        
        $s = DBI::prepare('UPDATE '.$table.' SET '.implode(', ', $placeholders).(count($where_parts) ? ' WHERE ('.implode(') AND (', $where_parts).')' : ''));
        $s->execute($values);
    }
}
