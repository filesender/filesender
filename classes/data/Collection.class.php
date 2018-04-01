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
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

/**
 *  Represents collection in the database
 */
class Collection extends DBObject
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
            'autoinc' => true,   //indicates that 'id' is auto-incremented
        ),
        'transfer_id' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'owner_id' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'name' => array(
            'type' => 'string',
            'size' => 255,
        ),
        'uid' => array(
            'type' => 'string',
            'null' => true,
            'size' => 60
        ),
        'info' => array(
            'type' => 'string',
            'null' => true,
            'size' => 2048
        ),
    );

    protected static $secondaryIndexMap = array(
        'transfer_id' => array( 
            'transfer_id' => array()
        ),
        'owner_id' => array( 
            'owner_id' => array()
        ),
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $transfer_id = null;
    protected $owner_id = null;
    protected $uid = null;
    protected $name = null;
    protected $info = null;
   
    /**
     * Related objects cache
     */
    private $transferCache = null;

    /**
     * Constructor
     * 
     * @param integer $id identifier of collection to load from database (null if loading not wanted)
     * @param array $data data to create the collection from (if already fetched from database)
     * 
     * @throws ClassificationNotFoundException
     */
    public function __construct($id = null, $data = null) {
    
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new ClassificationNotFoundException('id = '.$id);
        }

        // Fill properties from provided data
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Create a new collection (for upload)
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return Collection
     */
    public static function create(Transfer $transfer) {
        $collection = new self();
        
        $collection->transfer_id = $transfer->id;
        $collection->transferCache = $transfer;
        
        // Generate uid until it is indeed unique
        $collection->uid = Utilities::generateUID(function($uid, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.Collection::getDBTable().' WHERE uid = :uid');
            $statement->execute(array(':uid' => $uid));
            $data = $statement->fetch();
            if(!$data) Logger::info('Collection uid generation took '.$tries.' tries');
            return !$data;
        });

        return $collection;
    }
    
    /**
     * Delete the collection
     */
    public function Storage() {
        //TODO: referentially delete all Files & Namespaces belonging
        //TODO: to this collection
        beforeDelete::deleteClassification($this);
        
        Logger::info($this.' deleted');
    }
    
    /**
     * Get collection from uid
     * 
     * @param string $uid
     * 
     * @return Collection
     */
    public static function fromUid($uid) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE uid = :uid');
        $s->execute(array('uid' => $uid));
        $data = $s->fetch();
        
        if(!$data) throw ClassificationNotFoundException('uid = '.$uid);
        
        return self::fromData($data['id'], $data); // Don't query twice, use loaded data
    }
    
    /**
     * Get classifications from Transfer
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return array of Collection
     */
    public static function fromTransfer(Transfer $transfer) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id');
        $s->execute(array('transfer_id' => $transfer->id));
        $classifications = array();
        foreach($s->fetchAll() as $data) $classifications[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $classifications;
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
            'id', 'transfer_id', 'uid', 'name', 'info'
        ))) return $this->$property;
        
        if($property == 'transfer') {
            if(is_null($this->transferCache)) $this->transferCache = Transfer::fromId($this->transfer_id);
            return $this->transferCache;
        }
        
        if($property == 'owner') {
            return $this->transfer->owner;
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
    public function __set($property, $value) {
        if($property == 'name') {
            $this->name = (string)$value;
        }else if($property == 'info') {
            $this->info = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.', '.strlen($this->name)+strlen($this->info)+1.' bytes)';
    }
}
