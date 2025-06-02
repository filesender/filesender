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
 *  Represents collection in the database
 */
class CollectionType extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        //collection id, as in the database
        'id' => array(
            'type' => 'uint',   //data type of 'id'
            'size' => 'medium', //size of the integer stored in 'id' (in bytes, or otherwise)
            'primary' => true,  //indicates that 'id' is the primary key in the DB
        ),
        'name' => array(
            'type' => 'string',
            'size' => 60,
        ),
        'description' => array(
            'type' => 'string',
            'size' => 512
        ),
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $name = null;
    protected $description = null;
    
    /**
     * Predefined Enums
     */
    const UNKNOWN_ID     = 0; //"Undefined collection type";
    const TREE_ID        = 1; //"Root directory collection";
    const DIRECTORY_ID   = 2; //"Pathed directory collection";
    const LASTSTATIC_ID  = 999; //"Last static enum id";

    /**
     * Predefined Types
     */
    public static $UNKNOWN = null;
    public static $TREE = null;
    public static $DIRECTORY = null;
    public static $LASTSTATIC = null;
    public static $LATEST = null;

    /**
     * Constructor
     *
     * @param integer $id identifier of collection to load from database (null if loading not wanted)
     * @param array $data data to create the collection from (if already fetched from database)
     *
     * @throws ClassificationNotFoundException
     */
    public function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new ClassificationNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Initializes the CollectionType table
     *
     */
    public static function initialize()
    {
        if (!is_null(self::$UNKNOWN)) {
            return;
        }
        $types = array();
        
        // Load all CollectionTypes from database
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' ORDER BY :id');
        $s->execute(array(':id' => 'id'));
        foreach ($s->fetchAll() as $data) {
            $type = self::fromData($data['id'], $data);
            $types[$data['id']] = $type;
            self::$LATEST = $type;
        }
        
        if (array_key_exists(self::UNKNOWN_ID, $types)) {
            self::$UNKNOWN = $types[self::UNKNOWN_ID];
        } else {
            self::$UNKNOWN = new self();
            self::$UNKNOWN->id = self::UNKNOWN_ID;
            self::$UNKNOWN->name = 'UNKNOWN';
            self::$UNKNOWN->description = 'undefined collection type';
            self::$UNKNOWN->insert();
        }

        if (array_key_exists(self::TREE_ID, $types)) {
            self::$TREE = $types[self::TREE_ID];
        } else {
            self::$TREE = new self();
            self::$TREE->id = self::TREE_ID;
            self::$TREE->name = 'TREE';
            self::$TREE->description = 'directory tree collection';
            self::$TREE->insert();
        }

        if (array_key_exists(self::DIRECTORY_ID, $types)) {
            self::$DIRECTORY = $types[self::DIRECTORY_ID];
        } else {
            self::$DIRECTORY = new self();
            self::$DIRECTORY->id = self::DIRECTORY_ID;
            self::$DIRECTORY->name = 'DIRECTORY';
            self::$DIRECTORY->description = 'directory path collection';
            self::$DIRECTORY->insert();
        }

        if (array_key_exists(self::LASTSTATIC_ID, $types)) {
            self::$LASTSTATIC = $types[self::LASTSTATIC_ID];
        } else {
            self::$LASTSTATIC = new self();
            self::$LASTSTATIC->id = self::LASTSTATIC_ID;
            self::$LASTSTATIC->name = 'LASTSTATIC';
            self::$LASTSTATIC->description = 'last static collection type';
            self::$LASTSTATIC->insert();
            self::$LATEST = self::$LASTSTATIC;
        }
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
            'id', 'name', 'description'
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
     * @throws ClassificationBadHashException
     * @throws PropertyAccessException
     */
    public function __set($property, $value)
    {
        if ($property == 'name') {
            $this->name = (string)$value;
        } elseif ($property == 'description') {
            $this->description = (string)$value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
    
    /**
     * String caster
     *
     * @return string
     */
    public function __toString()
    {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.', '.(strlen($this->name)+strlen($this->description)+1).' bytes)';
    }
}
