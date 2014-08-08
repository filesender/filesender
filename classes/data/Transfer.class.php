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
        'user_id' => array(
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
     * Set selectors
     */
    const AVAILABLE = 'status = "available" ORDER BY created DESC';
    const EXPIRED = 'expires < DATE(NOW()) ORDER BY expires ASC';
    const FROM_USER = 'user_id = :user_id AND status="available" ORDER BY created DESC';
    
    /**
     * Properties
     */
    protected $id = null;
    protected $status = null;
    protected $user_id = null;
    protected $subject = null;
    protected $message = null;
    protected $created = 0;
    protected $expires = 0;
    protected $options = null;
    
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
     * Get transfers from user
     * 
     * @param mixed $user user object or user_id
     * 
     * @return array of Transfer
     */
    public static function fromUser($user) {
        if($user instanceof User) $user = $user->id;
        
        return self::all(self::FROM_USER, array(':user_id' => $user));
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
        
        $transfer->user_id = Auth::user()->id;
        $transfer->__set('expires', $expires);
        
        $transfer->created = time();
        $transfer->status = 'uploading';
        
        return $transfer;
    }
    
    /**
     * Save transfer in database
     */
    public function save() {
        if($this->id) {
            $this->updateRecord($this->toDBData(), 'id');
        }else{
            $this->insertRecord($this->toDBData());
            $this->id = (int)DBI::lastInsertId();
        }
    }
    
    /**
     * Delete the transfer related objects
     */
    public function beforeDelete() {
        foreach($this->files as $file) $this->removeFile($file);
        
        foreach($this->recipients as $recipient) $this->removeRecipient($recipient);
    }
    
    /**
     * Close the transfer
     */
    public function close() {
        if(!Config::get('audit_log_enabled')) {
            $this->delete();
        }else{
            $this->status = 'closed';
            $this->save();
        }
    }
    
    /**
     * Check if user owns current transfer
     * 
     * @param object $user other user or user_id to compare with
     * 
     * @return bool
     */
    public function isOwner($user) {
        return $this->owner->is($user);
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
        if(in_array($property, array('id', 'status', 'user_id', 'subject', 'message', 'created', 'expires', 'options'))) return $this->$property;
        
        if($property == 'user' || $property == 'owner') {
            return User::fromId($this->user_id);
        }
        
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
     * @throws BadStatusException
     * @throws BadExpireException
     * @throws PropertyAccessException
     */
    public function __set($property, $value) {
        if($property == 'status') {
            if(!in_array($value, array('uploading', 'available', 'closed'))) throw new BadStatusException($value);
            $this->status = (string)$value;
        }else if($property == 'subject') {
            $this->subject = (string)$value;
        }else if($property == 'message') {
            $this->message = (string)$value;
        }else if($property == 'expires') {
            if(preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}$`', $value)) {
                $value = strtotime($value);
            }
            
            if(!preg_match('`^[0-9]+$`', $value)) throw new BadExpireException($value);
            
            $value = (int)$value;
            if($value <= time() || $value > strtotime('+ '.Config::get('default_daysvalid').' day')) {
                throw new BadExpireException($value);
            }
            $this->expires = (string)$value;
        }else if($property == 'options') {
            $this->options = $value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Adds a file
     * 
     * @param string $name the file name
     * @param string $size the file size
     * 
     * @return object created file
     */
    public function addFile($name, $size) {
        // Create and save new recipient
        $file = File::create($this);
        $file->name = $name;
        $file->size = $size;
        $file->save();
        
        // Update local cache
        if(!is_null($this->files)) $this->files[$file->id] = $file;
        
        return $file;
    }
    
    /**
     * Removes a file
     * 
     * @param mixed $file file id or file object
     */
    public function removeFile($file) {
        if(!is_object($file)) $file = File::fromId($file);
        
        // Delete
        $file->delete();
        
        // Update local cache
        if(!is_null($this->files) && array_key_exists($file->id, $this->files)) unset($this->files[$file->id]);
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
