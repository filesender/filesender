<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2018, AARNet, Belnet, HEAnet, SURF, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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
 * Represents metadata about the aggregate statistics
 */
class AggregateStatisticMetadata extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
        ),
        // defaultable from here out
        'filesenderversion' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'lastsend' => array(
            'type' => 'datetime',
            'null' => true
        ),
    );

    protected static $secondaryIndexMap = array();

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'lastsend')
                        . '  from ' . self::getDBTable();

        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    
    /**
     * Properties
     */
    protected $id = null;
    protected $filesenderversion = null;
    protected $lastsend = null;
    
    
    /**
     * Constructor
     *
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the user from (if already fetched from database)
     *
     * @throws UserNotFoundException
     */
    public function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $d = $statement->fetch();
            if( $d ) {
                $data = $d;
            } else {
                $this->id = $id;
                $this->fillFromDBData($data);
                $this->lastsend = time();
                $this->insert();
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }

    public static function getLastSentDaysAgo()
    {
        $obj = self::ensure();
        $statement = DBI::prepare('SELECT * FROM '.self::getViewName().' WHERE id = :id');
        $statement->execute(array(':id' => $obj->id));
        $d = $statement->fetch();
        if( $d ) {
            return $d['lastsend_days_ago'];
        }
        return 0;
    }
    
    public static function enabled()
    {
        return AggregateStatistic::enabled();
    }


    /**
     * Called from the database.php update script to allow us to ensure
     * that all the tuples are in the database
     */
    public static function ensure()
    {
        $obj = new AggregateStatisticMetadata(1,array());
        $obj->filesenderversion = Version::CODE_VERSION;
        $obj->save();
        return $obj;
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
        if( array_key_exists($property, self::getDataMap())) {
            return $this->$property;
        }
        throw new PropertyAccessException($this, $property);
    }

    public function __set($property, $value)
    {
        if ($property == 'lastsend') {
            $this->lastsend = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
    
}
