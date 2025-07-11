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
class Authentication extends DBObject
{
    
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'big',
            'primary' => true,
            'autoinc' => true,
        ),
        'saml_user_identification_uid' => array(
            'type' => 'string',
            'size' => 170,
        ),
        'saml_user_identification_uid_hash' => array(
            'type' => 'string',
            'size' => 200,
            'null' => true
        ),
        'idpid' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'last_activity' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'comment' => array(
            'type' => 'string',
            'size' => 100,
            'null' => true
        ),
        'passwordhash' => array(
            'type' => 'string',
            'size' => '255',
            'null' => true
        ),
    );
    protected static $secondaryIndexMap = array(
        'saml_user_identification_uid' => array(
            'saml_user_identification_uid' => array(),
            'UNIQUE' => array()
        ),
        'saml_user_identification_uid_hash' => array(
            'saml_user_identification_uid_hash' => array()
        )
    );

    
    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $idpview[$dbtype] = 'select a.*,  idp.entityid as idp_entityid, idp.name as idp_name, idp.organization_name as idp_organization_name '
                              . ' FROM '
                               . self::getDBTable().' a '
                                     . ' LEFT JOIN '.call_user_func('IdP::getDBTable').' idp ON idp.id=a.id ';
        }
        
        return array( 'authidpview' => $idpview,
        );
    }

    
    /**
     * Properties
     */
    protected $id = null;
    protected $saml_user_identification_uid = null;
    protected $saml_user_identification_uid_hash = 0;
    protected $idpid = null;
    protected $created = 0;
    protected $last_activity = 0;
    protected $comment = null;
    protected $passwordhash = null;
    
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

    /**
     * Create or return the auth object
     */
    public static function ensure($saml_auth_uid, $comment = null, $saml_auth_idp = null)
    {
        $saml_uid = $saml_auth_uid;
        Logger::info('authentication::create(1) saml_uid ' . $saml_uid . ' saml_idp '. $saml_auth_idp);

        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE saml_user_identification_uid = :samluid');
        $statement->execute(array(':samluid' => $saml_uid));
        $data = $statement->fetch();
        if ($data) {
            $ret = static::createFactory(null, $data);
            $ret->fillFromDBData($data);
            if (!is_null($saml_auth_idp)) {

                $entityId = $saml_auth_idp;
                $idp = IdP::ensure($entityId);

                // only update if the idp has changed
                if ($ret->idpid != $idp->id) {
                    $ret->idpid = $idp->id;
                    $ret->save();
                }
            }
            Logger::info('authentication::create(2) FOUND AND RETURNING ' . $data['id']);
            return $ret;
        }
        
        $ret = static::createFactory();
        $ret->saml_user_identification_uid = $saml_uid;
        Logger::info('authentication::create(2) NOT FOUND! ' . $saml_uid);
        $ret->created = time();
        $ret->last_activity = $ret->created;
        Logger::info('authentication::create(3) ' . $saml_uid);
        $ret->updateHash();
        Logger::info('authentication::create(4) ' . $ret->id);
        Logger::info('authentication::create(5) ' . $ret->saml_user_identification_uid_hash);
        if (!is_null($saml_auth_idp)) {
            $entityId = $saml_auth_idp;
            $idp = IdP::ensure($entityId);
            $ret->idpid = $idp->id;
            
            Logger::info('authentication::create(6) ' . $entityId);
        }
        $ret->save();
        return $ret;
    }
    
    /**
     * Create or read the record for this authenticated user
     *
     * @param Auth $auth the auth information or the current user will be used. 
     *                   This matches the saml_user_identification_uid column.
     *
     * @return self
     */
    public static function ensureAuthIDFromSAMLUID($saml_auth_uid, $saml_auth_idp = null)
    {
        return self::ensure($saml_auth_uid,null,$saml_auth_idp)->id;
    }

    private function updateHash()
    {
        $h = sha1($this->saml_user_identification_uid);
        $this->saml_user_identification_uid_hash = $h;
        return $h;
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
            'id', 'saml_user_identification_uid', 'saml_user_identification_uid_hash', 'idpid', 'created','last_activity','passwordhash'
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
        if ($property == 'saml_user_identification_uid_hash') {
            $this->saml_user_identification_uid_hash = $value;
        } elseif ($property == 'idpid') {
            $this->idpid = $value;
        } elseif ($property == 'passwordhash') {
            $this->passwordhash = $value;
        } elseif ($property == 'password') {
            $this->passwordhash = password_hash($value, PASSWORD_ARGON2ID );
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
}
