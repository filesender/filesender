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
 * Represents an user in database
 */
class User extends DBObject {
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'string',
            'size' => 255,
            'primary' => true
        ),
        'organization' => array(
            'type' => 'string',
            'size' => 80,
            'null' => true
        ),
        'aup_ticked' => array(
            'type' => 'bool'
        ),
        'aup_last_ticked_date' => array(
            'type' => 'date',
            'null' => true
        ),
        'upload_preferences' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'voucher_preferences' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'last_activity' => array(
            'type' => 'datetime',
            'null' => true
        )
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $organization = null;
    protected $aup_ticked = false;
    protected $aup_last_ticked_date = 0;
    protected $upload_preferences = null;
    protected $voucher_preferences = null;
    protected $created = 0;
    protected $last_activity = 0;
    
    /**
     * From Auth if it makes sense
     */
    private $email = null;
    private $name = null;
    
    /**
     * Misc
     */
    private $isNew = true;
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the user from (if already fetched from database)
     * 
     * @throws UserNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new UserNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
        
        $this->isNew = false;
    }
    
    /***
     * Loads user from Auth attributes, handling cache
     * 
     * @param string $attributes
     * 
     * @return object user
     */
    public static function fromAttributes($attributes) {
        if(!is_array($attributes) || !array_key_exists('uid', $attributes) || !$attributes['uid']) throw new UserMissingUIDException();
        
        try {
            $user = self::fromId($attributes['uid']);
        } catch(UserNotFoundException $e) {
            $user = self::create($attributes['uid']);
        }
        
        if(array_key_exists('email', $attributes)) $user->email = $attributes['email'];
        if(array_key_exists('name', $attributes)) $user->name = $attributes['name'];
        
        return $user;
    }
    
    /**
     * Save user preferences in database
     */
    public function customSave() {
        if($this->isNew) {
            $this->insertRecord($this->toDBData());
        }else{
            $this->updateRecord($this->toDBData(), 'id');
        }
    }
    
    /**
     * Create a new user
     * 
     * @param string $id user id, mandatory
     * 
     * @return object user
     */
    public static function create($id) {
        $user = new self();
        
        $user->id = $id;
        $user->created = time();
        $user->last_activity = time();
        $user->isNew = true;
        
        return $user;
    }
    
    /**
     * Report last activity
     */
    public function reportActivity() {
        $this->last_activity = time();
        $this->save();
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
    public function __get($property) {
        if(in_array($property, array(
            'id', 'organization', 'aup_ticked', 'aup_last_ticked_date', 'upload_preferences',
            'voucher_preferences', 'created', 'last_activity', 'email', 'name'
        ))) return $this->$property;
        
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
    public function __set($property, $value) {
        if($property == 'organization') {
            $this->organization = (string)$value;
        }else if($property == 'aup_ticked') {
            $this->aup_ticked = (bool)$value;
        }else if($property == 'upload_preferences') {
            $this->upload_preferences = $value;
        }else if($property == 'voucher_preferences') {
            $this->voucher_preferences = $value;
        }else if($property == 'email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->email = (string)$value;
        }else if($property == 'name') {
            $this->name = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
}
