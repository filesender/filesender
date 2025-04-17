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
 * Represents a PKI public key
 */
class PublicKey extends DBObject
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
        // user.id associated with this tuple
        'userid' => array(
            'type' => 'uint',
            'size' => 'big'
        ),
        // DBConstantPublicKeyType
        'keytype' => array(
            'type' => 'uint',
            'size' => 'medium'
        ),
        'keydata' => array(
            'type' => 'text',
            'null' => true,
            'default' => null
        ),
        'created' => array(
            'type' => 'datetime',
        ),
        // is this the default key for this user (they may have many)
        'isdefault' => array(
            'type' => 'bool',
            'default' => true,
        ),
    );

    protected static $secondaryIndexMap = array(
        'created' => array(
            'created' => array()
        ),
        'userid' => array(
            'userid' => array()
        )
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
    protected $userid = null;
    protected $keytype = null;
    protected $keydata = null;
    protected $created = null;
    protected $isdefault = null;
    


    /**
     * Constructor
     *
     * @param integer $id identifier of object to load from database (null if loading not wanted)
     * @param array $data data to create the object from (if already fetched from database)
     *
     * @throws PKIKeyNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new PKIKeyNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
            if( $data['created'] ) {
                $this->created = $data['created'];
            }
        }
    }

    /**
     * This return the key if it is OK or throws an exception if there is something wrong.
     */
    private static function validateKeyData( $key, $keytype )
    {
        // We only handle PGP keys right now

        $rex = '/^(-----BEGIN PGP PUBLIC KEY BLOCK-----)([\n\r]*)([\/a-zA-Z0-9\n\.\:\+\ \=]{63}[\n\r]*)([\/a-zA-Z0-9\n\.\:\+\ \=]{1,64}[\n\r]*)([\/a-zA-Z0-9\n\.\:\+\ \=]{0,64}[\n\r]*)+(-----END PGP PUBLIC KEY BLOCK-----[\n\r]*)$/';        
        
        $key = filter_var( $key, FILTER_VALIDATE_REGEXP,
                           array( "flags" => FILTER_NULL_ON_FAILURE,
                                  "options" => array("regexp" => $rex ))
        );

        if( !$key ) {
            Logger::error("AAA issue with key");
            throw new PKIPGPBadPublicKeyException('');
        }
        
        return $key;
    }

    /**
     * Create a new public key
     */
    public static function create($key, $keytype = -1, $created = null)
    {
        $ret = new self();

        if( $keytype == -1 ) {
            $keytype = DBConstantPublicKeyType::lookup(DBConstantPublicKeyType::PGP);
        }

        self::validateKeyData( $key, $keytype );
        
        $ret->keytype = $keytype;
        $ret->keydata = $key;
        $ret->created = time();
        if( $created ) {
            $ret->created = $created;
        }
        $ret->save();
        return $ret;
    }

    public static function ensure( $userid, $key, $keytype = -1, $created = null )
    {
        if( $keytype == -1 ) {
            $keytype = DBConstantPublicKeyType::lookup(DBConstantPublicKeyType::PGP);
        }
        self::validateKeyData( $key, $keytype );
        
        $data = array();
        $data['userid'] = $userid;
        $data['keydata'] = $key;
        $data['keytype'] = $keytype;
        $data['created'] = $created;
        $data['isdefault'] = true;
        $r = self::fromData( null, $data );
        $r->created = time();
        return $r;
    }

    public static function exists( $id ) {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
        $statement->execute(array(':id' => $id));
        $data = $statement->fetch();
        if (!$data) {
            return false;
        }
        return true;
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
            'key'
         ))) {
            return $this->keydata;
        }
        if (in_array($property, array(
            'id',
            'keydata',
            'keytype',
            'created',
         ))) {
            return $this->$property;
        }
        throw new PropertyAccessException($this, $property);
    }

    const FROM_USER = "userid = :userid ORDER BY created DESC";
    const FROM_USER_DEF = "userid = :userid and isdefault ";
    
    public static function getDefaultForUser( $userid )
    {
        $r = self::all(self::FROM_USER_DEF, array(':userid' => $userid));
        if( $r ) $r = array_pop($r);
        return $r;
    }
    public static function allForUser( $userid )
    {
        return self::all(self::FROM_USER, array(':userid' => $userid));
    }
};
