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
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 *  Represents a collection of objects in the database
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
            'null' => true,
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
        )
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
    protected $transferCache = null;
    protected $parentCache = null;
    protected $filesCache = null;
    protected $typeCache = null;

    /**
     * Force database Table to use with all Collection children types
     */
    protected static $dataTable = 'Collections';
    
    /**
     * Overriding so children of Collection will still belong
     * to the Collection DBObject cache.
     *
     * @return type String: the class name that should be used for caching
     */
    public static function getCacheClassName()
    {
        return self::getClassName();
    }
    
    /**
     * Process the info value set on a newly created Collection of type
     */
    protected function processInfo()
    {
    }
    
    /**
     * Constructor
     *
     * @param integer $id identifier of collection to load from database (null if loading not wanted)
     * @param array $data data to create the collection from (if already fetched from database)
     *
     * @throws ClassificationNotFoundException
     */
    public function __construct($id = null, $data = null)
    {
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }

    /**
     * Allows overloaded creation of an object based off of it's properties
     *
     * @return type DBObject based object
     */
    public static function createFactory($id = null, $data = null)
    {
        $type_id = CollectionType::UNKNOWN_ID;

        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new ClassificationNotFoundException('id = '.$id);
            }
        }
        if (!is_null($data)) {
            $type_id = $data['type_id'];
        }
        
        return self::createFactoryType($type_id, $data);
    }
    
    /**
     * Create an empty Collection of the proper type
     *
     * @param CollectionType.id the collection type id
     * @param $data the array of properties to initialize the collection with
     *
     * @return Collection or one of it's children types
     */
    protected static function createFactoryType($type_id, $data = null)
    {
        if ($type_id == CollectionType::TREE_ID) {
            return new CollectionTree(null, $data);
        } elseif ($type_id == CollectionType::DIRECTORY_ID) {
            return new CollectionDirectory(null, $data);
        } else {
            return new static(null, $data);
        }
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
    public static function create(Transfer $transfer, CollectionType $type, $info)
    {
        $collection = static::createFactoryType($type->id);
        $collection->transfer_id = $transfer->id;
        $collection->transferCache = $transfer;
        
        $collection->type_id = $type->id;
        $collection->typeCache = $type;
        $collection->filesCache = array();
        $collection->info = $info;
        $collection->processInfo();
        
        return $collection;
    }
    
    /**
     * Delete the collection
     */
    public function beforeDelete()
    {
        if (!is_null($this->filesCache)) {
            foreach ($this->filesCache as $file_id => $filecollection) {
                $this->removeFile($filecollection);
            }
        }
        
        Logger::info($this.' deleted');
    }
    
    /**
     * Get collections from Transfer
     *
     * @param Transfer $transfer the relater transfer
     *
     * @return 2d array of <Collection.type_id, <Collection.id, Collection>>
     */
    public static function fromTransfer(Transfer $transfer)
    {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id ORDER BY type_id');
        $s->execute(array(':transfer_id' => $transfer->id));
        $collections = array();
        $collectionIds = array();
        foreach ($s->fetchAll() as $data) {
            $type_id = $data['type_id'];
            $id = $data['id'];
            if (!array_key_exists($type_id, $collections)) {
                $collections[$type_id] = array();
            }
            $collection = static::fromData($id, $data);
            $collections[$type_id][$id] = $collection;
            $collectionIds[] = $id;
        }

        $set = FileCollection::fromCollectionIds($collectionIds);
        foreach ($collections as $collectionTypes) {
            foreach ($collectionTypes as $id => $collection) {
                if (array_key_exists($id, $set)) {
                    $collection->filesCache = $set[$id];
                } else {
                    $collection->filesCache = array();
                }
            }
        }
        return $collections;
    }
    
    /**
     * Add a File to this collection, creating a FileCollection object via
     * FileCollection::add.
     *
     * @param File $file to add
     *
     * @return FileCollection instance
     */
    public function addFile(File $file)
    {
        if (is_null($this->filesCache)) {
            $this->filesCache = FileCollection::fromCollection($this->id);
        }

        // Check if already exists
        $file_id = $file->id;
        
        $matches = array_filter($this->filesCache, function ($filecollection) use ($file_id) {
            return ($filecollection->file_id == $file_id);
        });
        
        if (count($matches)) {
            return array_shift($matches);
        }

        $fc = FileCollection::add($this, $file);
        
        // Update local cache
        $this->filesCache[$file->id] = $fc;
        
        Logger::info($file.' added to '.$this);

        return $fc;
    }

    /**
     * Removes a file from this collection
     *
     * @param mixed $filecollection FileCollection, File, or file id
     */
    public function removeFile($filecollection)
    {
        if (!is_object($filecollection)) {
            $filecollection = FileCollection::create($this->id, $filecollection);
        } else {
            if ('File' === get_class($filecollection)) {
                $filecollection = FileCollection::create($this->id, $filecollection->id);
            } elseif ('FileCollection' !== get_class($filecollection)) {
                throw new InvalidFileCollectionException($this, $filecollection);
            }
        }
        $file_id = $filecollection->file_id;

        // Delete
        $filecollection->delete();
        
        // Update local cache
        if (!is_null($this->filesCache) &&
           array_key_exists($file_id, $this->filesCache)) {
            unset($this->filesCache[$file_id]);
        }
        
        Logger::info($filecollection.' removed from '.$this);
    }
    
    /**
     * Add a child collection to this collection. Note this will call
     * $child->save() and may call $this->save() to persist valid $id.
     *
     * @param Collection $child to add
     *
     * @return previous child's parent_id
     */
    public function addCollection(Collection $child)
    {
        $old_parent_id = $child->parent_id;
        $child->parent_id = $this->id;
        $child->parentCache = $this;
        $child->save();
        
        return $old_parent_id;
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
            'transfer_id', 'type_id', 'parent_id', 'info'
        ))) {
            return $this->$property;
        }
        
        if ($property == 'id') {
            if (is_null($this->id)) {
                $this->save();
            }
            return $this->id;
        }
        
        if ($property == 'transfer') {
            if (is_null($this->transferCache)) {
                $this->transferCache = Transfer::fromId($this->transfer_id);
            }
            return $this->transferCache;
        }
        
        if ($property == 'parent') {
            if (is_null($this->parentCache) && !is_null($this->parent_id)) {
                $this->parentCache = static::fromId($this->parent_id);
            }
            return $this->parentCache;
        }
        
        if ($property == 'type') {
            if (is_null($this->typeCache)) {
                $this->typeCache = CollectionType::fromId($this->type_id);
            }
            return $this->typeCache;
        }
        
        if ($property == 'files') {
            if (is_null($this->filesCache)) {
                $this->filesCache = FileCollection::fromCollection($this);
            }
            return $this->filesCache;
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
    public function __set($property, $value)
    {
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     *
     * @return string
     */
    public function __toString()
    {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->info.', '.(strlen($this->info)+1).' bytes)';
    }
}

