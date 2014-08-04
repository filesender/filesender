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
class Guestvoucher extends DBObject {
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => true
        ),
        'user_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'token' => array(
            'type' => 'string',
            'size' => 60,
            'unique' => true
        ),
        'email' => array(
            'type' => 'string',
            'size' => 255
        ),
        'transfers' => array(
            'type' => 'uint',
            'size' => 'medium'
        ),
        'subject' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'message' => array(
            'type' => 'text',
            'null' => true
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'expires' => array(
            'type' => 'datetime'
        )
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $user_id = null;
    protected $token = null;
    protected $email = null;
    protected $transfers = 0;
    protected $subject = null;
    protected $message = null;
    protected $options = null;
    protected $created = 0;
    protected $expires = 0;
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of guset voucher to load from database (null if loading not wanted)
     * @param array $data data to create the guset voucher from (if already fetched from database)
     * 
     * @throws GuestvoucherNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new GuestvoucherNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Create a new guset voucher
     * 
     * @param integer $email recipient email, mandatory
     * 
     * @return object guest voucher
     */
    public static function create($email) {
        $voucher = new self();
        
        $voucher->user_id = User::current()->id;
        $voucher->__set('email', $email); // Throws
        
        $voucher->created = time();
        $voucher->token = Utilities::generateUID();
        
        return $voucher;
    }
    
    /**
     * Get expired guest vouchers
     * 
     * @param integer $daysvalid guest voucher age limit (optionnal)
     * 
     * @return array guest voucher list
     */
    public static function getExpired($daysvalid = null) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE expires < NOW()');
        $s->execute();
        $vouchers = array();
        foreach($s->fetchAll() as $data) $vouchers[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $vouchers;
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
            'id', 'user_id', 'token', 'email', 'transfers', 'subject',
            'message', 'options', 'created', 'expires'
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
        if($property == 'subject') {
            $this->subject = (string)$value;
        }else if($property == 'message') {
            $this->message = (string)$value;
        }else if($property == 'options') {
            $this->options = $value;
        }else if($property == 'email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->email = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
}
