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
 * This creates a mapping of id to a string description in the database.
 * Once an integer id is used it should never be changed or deleted.
 *
 * Subclasses of DBConstant can easily create database tables with
 * these id->description mappings and add new entries which will be created
 * by the database.php script so that the system can assume the database is
 * up to date with these constant types.
 */
class DBConstant extends DBObject
{
    public static function createObject()
    {
        return new DBConstant();
    }
    
    // properties from dataMap
    protected $id   = null;
    protected $description = null;

    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => false,  // we maintain the ID->name mapping explicitly
        ),
        'description' => array(
            'type' => 'string',
            'size' => 60
        )
    );

    public static function getDataMap()
    {
        return self::$dataMap;
    }
    protected function getEnum()
    {
        Logger::haltWithErorr("getEnum() was called on the base db constant class");
    }
    
    protected static $secondaryIndexMap = array(
        'description' => array(
            'description' => array()
        )
    );

    

    protected function __construct($id = null, $data = null)
    {
        $selectSQL = 'SELECT * FROM '.self::getDBTable().' WHERE id = :id';
        
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare($selectSQL);
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new FileNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }

    
    public function __get($property)
    {
        if (in_array($property, self::getDataMap())) {
            return $this->$property;
        }
    }    
    public function __set($property, $value)
    {
        if ($property == 'id') {
            $this->id = $value;
        }
        if ($property == 'description') {
            $this->description = $value;
        }
    }

    /**
     * Called from the database.php update script to allow us to ensure
     * that all the tuples are in the database
     */
    public static function ensure()
    {
        $class = get_called_class();
        $obj = call_user_func($class.'::createObject');
        $newItems = $obj->getEnum();

        echo "db table " . self::getDBTable() . "\n";
        print_r($newItems);
        // Load all CollectionTypes from database
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' ORDER BY :id');
        $s->execute(array(':id' => 'id'));
        foreach ($s->fetchAll() as $data) {
            $description = $data['description'];
            unset($newItems[$description]);
        }

        foreach ($newItems as $k=>$v) {

            echo "k $k  v $v\n";
            $class = get_called_class();
            echo "class $class\n";
            $obj = call_user_func($class.'::createObject');
            $obj->id = $v;
            $obj->description = $k;
            echo "data ";
            print_r($obj->toDBData());
            echo  "\n";
            echo "dbtable1 " . $obj->getDBTable() . "\n";
            echo "dbtable2 " . self::getDBTable() . "\n";
            echo "ID " . self::lookup($k) . "\n";
            $obj->insert();
        }
        
    }

    /**
     * Allow a lookup from the string description to it's ID number.
     */
    public static function lookup( $desc )
    {
        $class = get_called_class();
        $obj = call_user_func($class.'::createObject');
        $d = $obj->getEnum();
        if( array_key_exists( $desc, $d )) {
            return $d[$desc];
        }
        Logger::haltWithErorr("unknown database constant was passed to lookup() $desc");
    }
}

