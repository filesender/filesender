<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 * Represents an user in database
 */
class Metadata extends DBObject
{
    
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => true,
        ),
        'schemaversion' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'message' => array(
            'type' => 'text'
        )
    );


    
    /**
     * Properties
     */
    protected $id = null;
    protected $schemaversion = null;
    protected $created = 0;
    protected $message = '';
    
    
    /**
     * Constructor
     *
     * @param integer $id identifier of record to load from database (null if loading not wanted)
     * @param array $data data to create the record from (if already fetched from database)
     *
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
        }
        
        if ($data) {
            $this->fillFromDBData($data);
        } else {
            $this->id = $id;
            $this->created = time();
        }
    }

    public static function getLatestUsedSchemaVersion()
    {
        if (!Database::tableExists(self::getDBTable())) {
            return DatabaseSchemaVersions::VERSION_MIN;
        }
        
        try {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' order by schemaversion desc limit 1');
            $statement->execute(array());
            $data = $statement->fetch();
            if ($data) {
                return $data['schemaversion'];
            }
            return DatabaseSchemaVersions::VERSION_MIN;
        } catch (Exception $e) {
            echo $e;
            return DatabaseSchemaVersions::VERSION_MIN;
        }
    }

    public static function add($message)
    {
        $ret = new self();

        $ret->schemaversion = DatabaseSchemaVersions::VERSION_CURRENT;
        $ret->created = time();
        $ret->message = $message;

        $ret->save();
        return $ret;
    }
    
    /**
     * Create a new record
     *
     * @param string $id record id, mandatory
     *
     * @return record
     */
    public static function create($id)
    {
        return self::fromId($id);
    }
    
    
    
    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @throws PropertyAccessException
     *
     * @return property value
     */
    public function __get($property)
    {
        if (in_array($property, array(
            'id', 'schemaversion', 'created', 'message'
        ))) {
            return $this->$property;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     *
     * @throws BadVoucherException
     * @throws BadStatusException
     * @throws BadExpireException
     * @throws PropertyAccessException
     */
    public function __set($property, $value)
    {
        if ($property == 'message') {
            $this->message = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
}
