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
 * Represents a transfer in database
 * 
 * @property array $files related files
 * @property array $recipients related recipients
 */
class Transfer extends DBObject {
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
        'uid' => array(
            'type' => 'string',
            'size' => 250
        ),
        'subject' => array(
            'type' => 'string',
            'size' => 250,
            'null' => true
        ),
        'message' => array(
            'type' => 'text',
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'expires' => array(
            'type' => 'datetime'
        ),
        'status' => array(
            'type' => 'string',
            'size' => 32
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        )
    );
    
    /**
     * By voucher cache
     */
    private static $by_voucher = array();
    
    /**
     * Properties
     */
    protected $id = null;
    protected $voucher = null;
    protected $status = null;
    protected $from = null;
    protected $subject = null;
    protected $message = null;
    protected $created = 0;
    protected $expires = 0;
    
    /**
     * Related objects cache
     */
    private $files = null;
    private $recipients = null;
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of transfer to load from database (null if loading not wanted)
     * @param array $data data to create the transfer from (if already fetched from database)
     * 
     * @throws TransferNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new TransferNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
    }
    
    /***
     * Loads transfer from voucher, handling cache
     * 
     * @param string $voucher
     * 
     * @throws TransferNotFoundException
     * 
     * @return object transfer
     */
    public static function fromVoucher($voucher) {
        if(array_key_exists($voucher, self::$by_voucher)) return self::$by_voucher[$voucher];
        
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE voucher = :voucher');
        $statement->execute(array(':voucher' => $voucher));
        $data = $statement->fetch();
        if(!$data) throw new TransferNotFoundException('voucher = '.$voucher);
        
        $transfer = self::fromData($data['id'], $data);
        self::$by_voucher[$voucher] = $transfer;
        
        return $transfer;
    }
    
    /**
     * Create a new transfer (ie begin upload)
     * 
     * @param integer $expiry expiration date (timestamp), mandatory
     * 
     * @return object transfer
     */
    public static function create($expires) {
        $transfer = new self();
        
        $transfer->from = User::current()->uid;
        $transfer->expires = $expires;
        
        $transfer->created = time();
        $transfer->status = 'uploading';
        $transfer->voucher = Utilities::generateUID();
        
        return $transfer;
    }
    
    /**
     * Get expired transfers
     * 
     * @param integer $daysvalid transfer age limit (optionnal)
     * 
     * @return array transfer list
     */
    public static function getExpired($daysvalid = null) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE expires < NOW()');
        $s->execute();
        $transfers = array();
        foreach($s->fetchAll() as $data) $transfers[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $transfers;
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
        if(in_array($property, array('id', 'voucher', 'status', 'from', 'subject', 'message', 'created', 'expires'))) return $this->$property;
        
        if($property == 'files') {
            if(is_null($this->files)) $this->files = File::fromTransfer($this);
            return $this->files;
        }
        
        if($property == 'recipients') {
            if(is_null($this->recipients)) $this->recipients = Recipient::fromTransfer($this);
            return $this->recipients;
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
    public function __set($property, $value) {
        if($property == 'voucher') {
            if(!preg_match($config->voucher_regexp, $value)) throw new BadVoucherException($value);
            $this->voucher = (string)$value;
        }else if($property == 'status') {
            if(!in_array($value, array('uploading', 'available'))) throw new BadStatusException($value);
            $this->status = (string)$value;
        }else if($property == 'subject') {
            $this->subject = (string)$value;
        }else if($property == 'message') {
            $this->message = (string)$value;
        }else if($property == 'expires') {
            $value = (int)$value;
            if($value <= time() || $value > strtotime('+ '.Config::get('default_daysvalid').' day')) {
                throw new BadExpireException($value);
            }
            $this->expires = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Adds a recipient
     * 
     * @param string $email email to add as recipient
     * 
     * @return object created recipient
     */
    public function addRecipient($email) {
        // Create and save new recipient
        $recipient = Recipient::create($this, $email);
        $recipient->save();
        
        // Update local cache
        if(!is_null($this->recipients)) $this->recipients[$recipient->id] = $recipient;
        
        return $recipient;
    }
    
    /**
     * Removes a recipient
     * 
     * @param mixed $recipient recipient id or recipient object
     */
    public function removeRecipient($recipient) {
        if(!is_object($recipient)) $recipient = Recipient::fromId($recipient);
        
        // Delete
        $recipient->delete();
        
        // Update local cache
        if(!is_null($this->recipients) && array_key_exists($recipient->id, $this->recipients)) unset($this->recipients[$recipient->id]);
    }
}
