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
 * Base class for database stored objects
 */
class DBObject
{
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
     *   - size (for int types) : small, medium, big (defaults to medium)
     *   - size (for string types) : string length
     *   - values (for enum types) : array of possible values
     *   - null : bool indicating if field can be null
     *   - primary : bool indicating if field is primary key
     *   - autoinc : bool indicating if field is auto-incremented
     *   - unique : bool indicating if field is unique or string pointing unicity column set
     *   - default : default value for this field
     */
    protected static $dataMap = array();

    protected static $viewMap = array();
    
    /**
     * Defines secondary indexes for this table
     *
     * Note that the IndexName you give will have the tablename_ prepended to it for you.
     * So you can just say key1_key2 to define the IndexName if you like.
     *
     * Associative array of <IndexName> => IndexDefinition
     * Where IndexDefinition is an associative array of <field> => <field_def>
     *   <field_def> associative array of field definition entries in :
     *     - notused : This is an associative array to allow later expansion.
     */
    protected static $secondaryIndexMap = array();

    /**
     * DataMap getter
     *
     * @return array the class dataMap
     */
    public static function getDataMap()
    {
        return static::$dataMap;
    }

    public static function getViewMap()
    {
        return static::$viewMap;
    }
    
    /**
     * Secondary Index map getter
     *
     * @return array the class SecondaryIndexMap
     */
    public static function getSecondaryIndexMap()
    {
        return static::$secondaryIndexMap;
    }
    
    /**
     * Check if object is cached
     *
     * @param string $class class name
     * @param mixed $id unique identifier
     *
     * @return bool
     */
    public static function existsInCache($class, $id)
    {
        return array_key_exists($class, self::$objectCache) && array_key_exists($id, self::$objectCache[$class]);
    }
    
    /**
     * Get object from cache
     *
     * @param string $class class name
     * @param mixed $id unique identifier
     *
     * @return object
     */
    public static function getFromCache($class, $id)
    {
        if (!self::existsInCache($class, $id)) {
            return null;
        }
        
        return self::$objectCache[$class][$id];
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
    public static function purgeCache($class = null, $id = null)
    {
        if ($class) {
            if (!array_key_exists($class, self::$objectCache)) {
                return false;
            }
            
            if ($id) {
                if (!array_key_exists($id, self::$objectCache[$class])) {
                    return false;
                }
                unset(self::$objectCache[$class][$id]);
            } else {
                self::$objectCache[$class] = array();
            }
        } else {
            self::$objectCache = array();
        }
        
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
    public static function fromId($id)
    {
        $class = static::getCacheClassName();
        
        $object = self::getFromCache($class, $id);
        if ($object) {
            return $object;
        }
        
        $object = static::createFactory($id);
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
    public static function fromData($id, $data = null, $transforms = array())
    {
        $class = static::getCacheClassName();
        
        $object = self::getFromCache($class, $id);
        if (!$object) {
            $object = static::createFactory(null, $data);
        }
        
        $object->fillFromDBData($data, $transforms);
        
        self::$objectCache[$class][$id] = $object;
        return $object;
    }
    
    /**
     * Object comparison
     *
     * @param object $other other object or id
     *
     * @return bool
     */
    public function is($other)
    {
        if (is_object($other)) {
            return ($other instanceof static) && ($other->id == $this->id);
        }
        
        return $this->id == $other;
    }


    /**
     * Build and return an SQL statement. Used by all() and count().
     * 
     * @param string selectClause sql for what elements to SELECT
     * @param string $criteria sql criteria and/or pagination options
     * @param array $placeholders
     * 
     * @return Statement object
     */
    private static function buildStatement($selectClause, $criteria = null, $placeholders = array())
    {
        $tablename = static::getDBTable();
        if (is_array($criteria)) {
            if (array_key_exists('view', $criteria)) {
                $v = $criteria['view'];
                if( $v ) {
                    $tablename = $v;
                }
            }
        }
        $query = 'SELECT ' . $selectClause . ' FROM '. $tablename . ' ';
        $count = null;
        $offset = null;
        
        // Build filters if required
        if ($criteria) {
            $where = null;
            $order = null;
            $group = null;
            
            if (is_array($criteria)) {
                if (array_key_exists('query', $criteria)) {
                    $query  = $criteria['query'];
                }
                if (array_key_exists('where', $criteria)) {
                    $where  = $criteria['where'];
                }
                if (array_key_exists('order', $criteria)) {
                    $order  = $criteria['order'];
                }
                if (array_key_exists('group', $criteria)) {
                    $group  = $criteria['group'];
                }
                if (array_key_exists('count', $criteria)) {
                    $count  = (int)$criteria['count'];
                }
                if (array_key_exists('limit', $criteria)) {
                    $count  = (int)$criteria['limit'];
                }
                if (array_key_exists('offset', $criteria)) {
                    $offset = (int)$criteria['offset'];
                }
            } else {
                $where = $criteria;
            }
            
            if ($where) {
                $query .= ' WHERE '    . $where;
            }
            if ($group) {
                $query .= ' GROUP BY ' . $group;
            }
            if ($order) {
                $query .= ' ORDER BY ' . $order;
            }
            if ($count) {
                $query .= ' LIMIT '    . $count;
            }
            if ($offset) {
                $query .= ' OFFSET '   . $offset;
            }
        }
        
        // Look for primary key(s) name(s)
        $pk = array();
        foreach (static::getDataMap() as $k => $d) {
            if (array_key_exists('primary', $d) && $d['primary']) {
                $pk[] = $k;
            }
        }
        // Prepare query depending on contents
        if (preg_match('`\s+[^\s]+\s+IN\s+:[^\s]+\b`i', $query)) {
            $statement = DBI::prepareInQuery($query, array_filter($placeholders, 'is_array'));
        } else {
            $statement = DBI::prepare($query);
        }
        return $statement;
    }    
    /**
     * Get a set objects
     *
     * @param string $criteria sql criteria and/or pagination options
     * @param array $placeholders
     * @param callable $run will be applied to all objects, return values will replace objects and result will be filtered to remove nulls
     *
     * @return array of objects or page object
     */
    public static function all($criteria = null, $placeholders = array(), $run = null)
    {
        $selectClause = '*';
        if (is_array($criteria)) {
            if (array_key_exists('select', $criteria)) {
                $selectClause = $criteria['select'];
            }
        }
            
        $statement = self::buildStatement( $selectClause, $criteria, $placeholders );
        
        // run it
        $statement->execute($placeholders);
        
        // Fetch records, register them with id build from primary key(s) value(s)
        $records = $statement->fetchAll();
        return self::convertTableResultsToObjects( $records, $run );
    }

    /**
     * Get count(*) for query
     *
     * @param string $criteria sql criteria and/or pagination options
     * @param array $placeholders
     *
     * @return int count
     */
    public static function count($criteria = null, $placeholders = array())
    {
        $statement = self::buildStatement('count(*) as count',$criteria, $placeholders);
        
        // run it
        $statement->execute($placeholders);

        $data = $statement->fetch();
        return $data['count'];
    }
    
    /**
     * convert the result of an sql query on this table to a set objects
     *
     * @param string $records contain full information for tuples in this table
     * @param callable $run will be applied to all objects, return values will replace objects and result will be filtered to remove nulls
     *
     * @return array of objects or page object
     */
    public static function convertTableResultsToObjects( $records, $run = null )
    {
        // Look for primary key(s) name(s)
        $pk = array();
        foreach (static::getDataMap() as $k => $d) {
            if (array_key_exists('primary', $d) && $d['primary']) {
                $pk[] = $k;
            }
        }
        
        $objects = array();
        
        foreach ($records as $r) {
            $id = array();
            foreach ($pk as $k) {
                $id[] = $r[$k];
            }
            $id = implode('-', $id);
            
            $objects[$id] = static::fromData($id, $r);
        }
        
        // Apply callback if provided
        if ($run && is_callable($run)) {
            $new_things = array();
            foreach ($objects as $id => $o) {
                $objects[$id] = $run($o);
            }
            $objects = array_filter($objects, function ($o) {
                return !is_null($o);
            });
        }
        
        return $objects;
    }
    
    /**
     * Save in database
     */
    public function save()
    {
        // If child class has things to do before a save run that
        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }
        
        if (method_exists($this, 'customSave')) {
            // Child class has custom saver, run it
            $this->customSave();
        } else {
            // Normal save
            
            if ($this->id) {
                // Update record if object was loaded from existing one
                $this->updateRecord($this->toDBData(), 'id');
            } else {
                // Create it otherwise and get primary key back
                $pks = $this->insertRecord($this->toDBData());
                if (array_key_exists('id', $pks)) {
                    $this->id = (int)$pks['id'];
                }
            }
        }
        
        // Cache object
        self::$objectCache[static::getCacheClassName()][$this->id] = $this;
    }
    
    /**
     * Add to database
     */
    public function insert()
    {
        // Insert object
        $this->insertRecord($this->toDBData());
        // Cache object
        self::$objectCache[static::getCacheClassName()][$this->id] = $this;
    }
    
    /**
     * Delete from database
     */
    public function delete()
    {
        // If child class has things to do before instance is deleted run that
        if (method_exists($this, 'beforeDelete')) {
            $this->beforeDelete();
        }
        
        // Remove from database
        $s = DBI::prepare('DELETE FROM '.static::getDBTable().' WHERE id = :id');
        $s->execute(array(':id' => $this->id));
        
        // Remove from object cache
        self::purgeCache(get_called_class(), $this->id);
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
    protected function fillFromDBData($data, $transforms = array())
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        
        // Iterate over data
        foreach ($data as $field_name => $value) {
            if (!array_key_exists($field_name, static::$dataMap)) {
                continue;
            } // Ignore non-mapped data
            
            $dfn = static::$dataMap[$field_name];
            
            // Basic types transformations/casting
            if (!is_null($value) || !array_key_exists('null', $dfn) || !$dfn['null']) {
                switch ($dfn['type']) {
                    case 'int':
                    case 'uint':
                        $value = (int)$value;
                        break;
                        
                    case 'float':
                        $value = (float)$value;
                        break;
                        
                    case 'datetime':
                    case 'date':
                        if (!$value && array_key_exists('null', $dfn) && $dfn['null']) {
                            $value = null;
                        } elseif (!$value) {
                            $value = 0;
                        } else {
                            $value = (int)strtotime($value); // UNIX timestamp
                        }
                        break;
                        
                    case 'time':
                        $value = (int)(strtotime($value) % (24 * 3600)); // Offset since 0h00
                        break;
                        
                    case 'bool':
                        $value = (bool)$value;
                        break;
                }
            }
            
            // On-the-fly transforms
            if (array_key_exists('transform', $dfn)) {
                switch ($dfn['transform']) {
                case 'json':
                    $value = is_null($value) ? null : json_decode($value);
                    break;
            }
            }
            
            // Do we asked for further transformations ?
            if (array_key_exists($field_name, $transforms)) {
                if (is_string($transforms[$field_name]) || is_callable($transforms[$field_name])) {
                    $transforms[$field_name] = array($transforms[$field_name]);
                }
                
                if (is_array($transforms[$field_name])) {
                    foreach ($transforms[$field_name] as $transform) {
                        if (is_string($transform)) {
                            $field_name = $transform;
                        } // Key change
                        if (is_callable($transform)) {
                            $value = $transform($value);
                        } // Value transformation
                    }
                } elseif (!$transform[$field_name]) {
                    continue;
                } // Null/false transform skips entry
            }
            if (property_exists($this, $field_name)) {
                $this->$field_name = $value;
            }
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
    public function toDBData($transforms = array())
    {
        $field_names = array_keys(static::$dataMap);
        
        $data = array();
        
        // Iterate over keys
        foreach ($field_names as $field_name) {
            $property_name = $field_name;
            $value_transform = null;
            
            // Does the value need transformation ?
            if (array_key_exists($field_name, $transforms)) {
                if (is_string($transforms[$field_name]) || is_callable($transforms[$field_name])) {
                    $transforms[$field_name] = array($transforms[$field_name]);
                }
                
                if (is_array($transforms[$field_name])) {
                    foreach ($transforms[$field_name] as $transform) {
                        if (is_string($transform)) {
                            $property_name = $transform;
                        } // Key change
                        if (is_callable($transform)) {
                            $value_transform = $transform;
                        } // Value transformation
                    }
                }
            }
            
            $dfn = static::$dataMap[$field_name];
            $value = $this->$property_name;
            
            // Does the value need transformation ?
            if ($value_transform) {
                $value = $value_transform($value);
            } else {
                if (array_key_exists('transform', $dfn)) {
                    switch ($dfn['transform']) {
                    case 'json':
                        if (!is_null($value) || !array_key_exists('null', $dfn) || !$dfn['null']) {
                            $value = json_encode($value);
                        }
                        break;
                }
                }
                
                if (is_null($value) && array_key_exists('null', $dfn) && $dfn['null']) {
                    $value = null;
                } else {
                    switch ($dfn['type']) { // Basic types transformations/casting
                    case 'datetime':
                        $value = date('Y-m-d H:i:s', $value); // UNIX timestamp
                        break;
                        
                    case 'date':
                        $value = date('Y-m-d', $value); // UNIX timestamp
                        break;
                        
                    case 'time':
                        $value = date('H:i:s', $value); // Offset since 0h00
                        break;
                        
                    case 'bool':
                        $value = $value ? '1' : '0';
                        break;
                }
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
    public static function getDBTable()
    {
        $class = get_called_class();
        $name = property_exists($class, 'dataTable') ? static::$dataTable : $class.'s';
        
        if (Config::exists('db_table_prefix')) {
            $name = Config::get('db_table_prefix').$name;
        }
        
        return $name;
    }
    
    /**
     * Insert object as new record
     *
     * @param array $data database compliant data such as returned by toDBData
     */
    public static function insertRecord($data)
    {
        $table = self::getDBTable();
        // Remove autoinc keys
        foreach (static::$dataMap as $field_name => $dfn) {
            if (array_key_exists('autoinc', $dfn) && $dfn['autoinc']) {
                if (array_key_exists($field_name, $data)) {
                    unset($data[$field_name]);
                }
            }
        }
        
        // Insert data
        $values = array();
        foreach ($data as $field_name => $value) {
            $values[':'.$field_name] = $value;
        }
        $s = DBI::prepare('INSERT INTO '.$table.'('.implode(', ', array_keys($data)).') VALUES(:'.implode(', :', array_keys($data)).')');
        $s->execute($values);
        
        // Get primary key(s) back
        $pks = array();
        foreach (static::$dataMap as $field_name => $dfn) {
            if (array_key_exists('autoinc', $dfn) && $dfn['autoinc']) {
                $pks[$field_name] = DBI::lastInsertId($table.'_'.$field_name.'_seq');
            }
        }
        return $pks;
    }
    
    /**
     * Update object record
     *
     * @param array $data database compliant data such as returned by toDBData
     * @param string $key_name field_name to use as primary key (string or array of strings)
     * @param string $where where clause extension (optionnal)
     */
    public static function updateRecord($data, $key_name, $where = null)
    {
        $table = static::getDBTable();
        
        $placeholders = array();
        $values = array();
        
        // Filter
        $key_names = (array) $key_name;
        $key_names = array_filter($key_names);
        
        // Build update pairs
        foreach ($data as $field_name => $value) {
            if (!in_array($field_name, $key_names)) {
                $placeholders[] = $field_name.' = :'.$field_name;
            }
            $values[':'.$field_name] = $value;
        }
        
        // Build filter
        $where_parts = array();
        foreach ($key_names as $key_name) {
            $where_parts[] = $key_name.' = :'.$key_name;
        }
        if ($where) {
            $where_parts[] = $where;
        }
        
        // Run the query
        $s = DBI::prepare('UPDATE '.$table.' SET '.implode(', ', $placeholders).(count($where_parts) ? ' WHERE ('.implode(') AND (', $where_parts).')' : ''));
        $s->execute($values);
    }
    
    /**
     * Allows overloaded creation of an object based off of it's properties
     *
     * @return type DBObject based object
     */
    public static function createFactory($id = null, $data = null)
    {
        return new static($id, $data);
    }
    
    /**
     * Allows to get the class name
     *
     * @return type String: the class name
     */
    public static function getClassName()
    {
        return get_called_class();
    }
    
    /**
     * Allows overloading the DBObject cache class name
     *
     * @return type String: the class name that should be used for caching
     */
    public static function getCacheClassName()
    {
        return static::getClassName();
    }
    
    /**
     * String caster
     *
     * @return string
     */
    public function __toString()
    {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved');
    }

    public static function getViewName()
    {
        return strtolower(self::getDBTable()) . 'view';
    }

    /**
     * This is like count(*) but allows statictics to be used to very quickly
     * return an answer that is close to the real number
     */
    public static function countEstimate()
    {
        $datakey = 'rows';
        $placeholders = array();

        $dbtype = Config::get('db_type');
        if ($dbtype == 'pgsql') {
            $query = "SELECT reltuples AS rows FROM pg_class WHERE lower(relname) = lower('".static::getDBTable()."')";
            $datakey = 'rows';
        }
        if ($dbtype == 'mysql') {
            $query = "show table status like '".static::getDBTable()."'";
            $datakey = "Rows";
        }
        $statement = DBI::prepare($query);
        $statement->execute($placeholders);
        $data = $statement->fetch();
        return $data[$datakey];
    }


    /**
     * Check if this object has an expiry date that can be extended
     *
     * Subclasses wishing to use this must: 
     *   -- declare a const OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY = "allow_guest_expiry_date_extension";
     *        The object or class name should replace "_guest_" in the above string.
     *   -- have a database column expiry_extensions of type int with matching member 
     *      variable to track the number of extensions
     *   -- define the listed exception classes
     *
     * @param string OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY a config key to get
     *                  and $configKey_admin to check what extension might be possible
     * @param bool $throw throw on error
     *
     * @return int number of days the object expiry date can be extended by
     *
     * @throws $className . ExpiryExtensionNotAllowedException
     * @throws $className . ExpiryExtensionCountExceededException
     */
    public function getObjectExpiryDateExtension($throw = true)
    {
        $pattern = null;
        $configKey = get_class($this)::OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY;
        
        if( Auth::isAdmin()) {
            $pattern = Config::get($configKey . '_admin');
        }
        if( !$pattern ) {
            $pattern = Config::get($configKey);
        }
        
        if (!$pattern) {
            if ($throw) {
                $exceptName = get_class($this) . "ExpiryExtensionNotAllowedException";
                throw new $exceptName($this);
            }
            return 0;
        }
        
        if (!is_array($pattern)) {
            $pattern = array($pattern);
        }

        // Get nth
        $index = (int)$this->expiry_extensions;
        
        if ($index < count($pattern) && (!is_bool($pattern[$index]))) {
            $duration = (int)$pattern[$index];
        } else {
            $last = array_pop($pattern);
            
            if (count($pattern) && is_bool($last) && $last) {
                $duration = array_pop($pattern);
            } else {
                if ($throw) {
                    $exceptName = get_class($this) . "ExpiryExtensionCountExceededException";
                    throw $exceptName($this);
                }
                return 0;
            }
        }
        
        if ((count($pattern) == 2) && is_bool($pattern[0]) && $pattern[0]) { // Infinite
            return (int)$pattern[1];
        }
            
        return $duration;
    }

    /**
     * Extend expiry date (if enabled) see getObjectExpiryDateExtension for object requirements
     *
     * @throws $className . ExpiryExtensionNotAllowedException
     */
    public function extendObjectExpiryDate()
    {
        $configKey = get_class($this)::OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY;
        $duration = $this->getObjectExpiryDateExtension(); // throws
        
        if (!$duration) { // Should not happend unless config is garbled
            $exceptName = get_class($this) . "ExpiryExtensionNotAllowedException";
            throw new $exceptName($this);
        }
        
        $this->expires += $duration * 24 * 3600;
        
        $this->expiry_extensions++;
        
        $this->save();
    }
    
}
