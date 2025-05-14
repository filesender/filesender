<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2022, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
 * Represents an IdP
 */
class IdP extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'big',
            'primary' => true,
            'autoinc' => true
        ),
        'entityid' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'name' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'description' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'organization_name' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'organization_display_name' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'url' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'organization_url' => array(
            'type' => 'string',
            'size' => 170,
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime',
        ),
        'updated' => array(
            'type' => 'datetime',
            'null' => true
        ),
    );

    protected static $secondaryIndexMap = array(
        'entityid' => array(
            'entityid' => array()
        ),
        'name' => array(
            'name' => array()
        ),
    );


    public static function getViewMap()
    {
        $constable = DBConstantAVProgram::getDBTable();
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Properties
     */
    protected $id = null;
    protected $entityid = null;
    protected $name = null;
    protected $description = null;
    protected $organization_name = null;
    protected $organization_display_name = null;
    protected $url = null;
    protected $organization_url = null;
    protected $created = null;
    protected $updated = null;
    protected $changed = false;
    


    /**
     * Constructor
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

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
            
//            if( $data['created'] ) {
//                $this->created = $data['created'];
//            }
            $this->save();
        }
    }


    /**
     * Create a new object
     */
    public static function create($key, $entityid, $created = null)
    {
        $ret = new self();

        if( $keytype == -1 ) {
            $keytype = DBConstantPublicKeyType::lookup(DBConstantPublicKeyType::OpenPGP);
        }

        
        $ret->keytype = $keytype;
        $ret->keydata = $key;
        $ret->created = time();
        $ret->updated = time();
        if( $created ) {
            $ret->created = $created;
        }
        $ret->save();
        return $ret;
    }


    public static function ensure( $entityid, $name = null, $created = null )
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE entityid = :entityid');
        $statement->execute(array(':entityid' => $entityid));
        $data = $statement->fetch();
        if ($data) {
            return IdP::fromId($data['id']);
        }
        
        
        $data = array();
        $data['entityid'] = $entityid;
        $data['name'] = $name;
        $r = self::fromData( null, $data );
        $r->created = time();
        return $r;
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
            'id',
            'entityid',
            'name',
            'description',
            'organization_name',
            'organization_display_name',
            'url',
            'organization_url',
            'created',
         ))) {
            return $this->$property;
        }
        throw new PropertyAccessException($this, $property);
    }

    public function __set($property, $value)
    {
        if (in_array($property, array(
            'name',
            'description',
            'organization_name',
            'organization_display_name',
            'url',
            'organization_url',
        ))) {
            $this->$property = $value;
            $this->changed = true;
        }
    }

    public function saveIfChanged()
    {
        if( $this->changed )
        {
            $this->changed = false;
            $this->updated = time();
            $this->save();
        }
    }
};
