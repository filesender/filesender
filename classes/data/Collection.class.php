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
        'parent_id' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'type_id' => array(
            'type' => 'uint',
            'size' => 'medium',
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
        'type_id' => array( 
            'type_id' => array()
        ),
        'parent_id' => array( 
            'parent_id' => array()
        ),
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $transfer_id = null;
    protected $parent_id = null;
    protected $type_id = null;
    protected $info = null;
   
    /**
     * Related objects cache
     */
    private $transferCache = null;
    private $parentCache = null;
    private $typeCache = null;

    /**
     * Set the info for a DirTree collection
     * 
     * @param string $path the root path name for the dirtree
     */
    protected function setDirtreeInfo($path) {
        $this->name = $pathName;
        $pos = strrpos($pathName, '/');
        $collection = null;
      
        if (!($pos === false)) {
           $this->name = substr($pathName, $pos + 1);
           $collection = $transferCache->addCollection(substr($pathName, $pos - 1), $this);
        }

        if ($collection != null) {
           $collectionCache[$collection->id] = $collection
        }
    }

    /**
     * Set the info of a Collection, which may cause further processing
     * dependant on the collection's type
     * 
     * @param string $info specific information about this instance of a collection
     * 
     * @throws PropertyAccessException
     */
    protected function setInfo($info) {
        if (($this->info != null) &&
           (($type = CollectionType::$DIRTREE) ||
            ($type = CollectionType::$DIRECTORY))) {
           throw new PropertyAccessException($this, $type->$name.' '.$info);
        }
            
        if ($type = CollectionType::$DIRTREE) {
            setDirtreeInfo($info);
        }
        else
        if ($type = CollectionType::$DIRECTORY) {
            setDirectoryInfo($info);
        }
        else {
            $this->$info = $info;
        }
    }
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of collection to load from database (null if loading not wanted)
     * @param array $data data to create the collection from (if already fetched from database)
     * 
     * @throws ClassificationNotFoundException
     */
    public function __construct($id = null, $data = null) {

        CollectionType::initialize();
        
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
     * Create a new Collection
     * 
     * @param Transfer $transfer the relater transfer
     * @param CollectionType $type the type of collection
     * @param $info specific information about this collection instance
     * 
     * @return Collection
     */
    public static function create(Transfer $transfer, CollectionType $type, $info) {
        $collection = new self();
        
        $collection->transfer_id = $transfer->id;
        $collection->transferCache = $transfer;
        
        $collection->type_id = $type->id;
        $collection->typeCache = $type;

        $collection->__set('info', $info);
        
        return $collection;
    }
    
    /**
     * Get collections from Transfer
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return array of Collection
     */
    public static function fromTransfer(Transfer $transfer) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id');
        $s->execute(array('transfer_id' => $transfer->id));
        $collections = array();
        foreach($s->fetchAll() as $data) $collections[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $collections;
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
            'id', 'transfer_id', 'type_id', 'parent_id', 'info'
        ))) return $this->$property;
        
        if($property == 'transfer') {
            if(is_null($this->transferCache)) $this->transferCache = Transfer::fromId($this->transfer_id);
            return $this->transferCache;
        }
        
        if($property == 'parent') {
            return $this->parentCache;
        }

        if($property == 'type') {
            return $this->typeCache;
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
        if($property == 'info') {
            setInfo((string)$value);
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->info.', '.strlen($this->info)+1.' bytes)';
    }
}
