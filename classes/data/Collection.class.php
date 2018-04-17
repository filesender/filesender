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
    private $transferCache = null;
    private $parentCache = null;
    private $filesCache = null;
    private $typeCache = null;

    /**
     * Set the info of a Collection, which may cause further processing
     * dependant on the collection's type
     * 
     * @param Collection $this the Collection instance who's info is being set
     * @param string $info specific information about this instance of a collection
     */
    protected function setInfo($info) {
        Logger::info('Collection::setInfo');
        $this->info = $info;
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
        if ($type === CollectionType::$TREE) {
        Logger::info(get_called_class().' CREATING CollectionTree');
            $collection = new CollectionTree();
        }
        else
        if ($type === CollectionType::$DIRECTORY) {
        Logger::info(get_called_class().' CREATING CollectionDirectory');
            $collection = new CollectionDirectory();
        }
        else {
        Logger::info(get_called_class().' CREATING Collection');
            $collection = new self();
        }
        
        $collection->transfer_id = $transfer->id;
        $collection->transferCache = $transfer;
        
        $collection->type_id = $type->id;
        $collection->typeCache = $type;
        $collection->filesCache = array();
 
        $collection->setInfo($info);
        
        return $collection;
    }
    
    /**
     * Get collections from Transfer
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return 2d array of <Collection.type_id, <Collection.id, Collection>>
     */
    public static function fromTransfer(Transfer $transfer) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id ORDER BY type_id');
        $s->execute(array('transfer_id' => $transfer->id));
        $collections = array();
        foreach($s->fetchAll() as $data) {
            if(is_null($collections[$data[$type_id]])) {
                $collections[$data[$type_id]] = array();
            }
            $collections[$data['type_id']][$data['id']] = self::fromData($data['id'], $data);
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
    public function addFile(File $file) {
        if(is_null($this->filesCache)) {
            $this->filesCache = FileCollection::fromCollection($this->id);
        }

        // Check if already exists
        $file_id = $file->id;
        
        $matches = array_filter($this->filesCache, function($filecollection) use($file_id) {
            return ($filecollection->file_id == $file_id);
        });
        
        if(count($matches)) return array_shift($matches);

        $fc = FileCollection::add($this, $file);
        
        // Update local cache
        $this->filesCache[$file->id] = $fc;
        
        Logger::info($file.' added to '.$this);

        return $fc;
    }
    
    /**
     * Add a child collection to this collection. Note this will call
     * $child->save() and may call $this->save() to persist valid $id.
     * 
     * @param Collection $child to add
     * 
     * @return previous child's parent_id
     */
    public function addCollection(Collection $child) {
        $old_parent_id = $child->parent_id;
        $child->parent_id = $this->id;
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
    protected static function getProperty($c, $property) {
        if(in_array($property, array(
            'transfer_id', 'type_id', 'parent_id', 'info'
        ))) return $c->$property;
        
        if($property == 'id') {
            if (is_null($c->id)) {
                save();
            }
            return $c->id;
        }
        
        if($property == 'transfer') {
            if(is_null($c->transferCache)) $c->transferCache = Transfer::fromId($c->transfer_id);
            return $c->transferCache;
        }
        
        if($property == 'parent') {
            if(is_null($c->parentCache)) $c->parentCache = Collection::fromId($c->parent_id);
            return $c->parentCache;
        }
        
        if($property == 'type') {
            if(is_null($c->typeCache)) $c->typeCache = CollectionType::fromId($c->type_id);
            return $c->typeCache;
        }
        
        if($property == 'files') {
            if(is_null($c->filesCache)) $c->filesCache = FileCollection::fromCollection($c->id);
            return $c->filesCache;
        }
        
        throw new PropertyAccessException($c, $property);
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
        Logger::info(get_called_class().'::getPROP('.$property.')');
        return static::getProperty($this, $property);
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
            static::setInfo($this, (string)$value);
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->info.', '.(strlen($this->info)+1).' bytes)';
    }

}

/**
 *  Represents a directory tree Collection of subdirs and files
 *  It creates a File of mime type 'text/directory' so
 *  a uuid can be associated with a CollectionTree
 */
class CollectionTree extends Collection
{
    /**
     * Override database Table to use
     */
    protected static $dataTable = 'Collections';
    
    /**
     * Properties
     */
    protected $uuid = null;
   
    /**
     * Related objects cache
     */
    private $fileCache = null;
    
    /**
     * Loads the File object associated with the CollectionTree
     * 
     * @throws TreeFileCollectionException
     */
    protected function loadTreeFile() {
        // Throw an error if attempting to change after already created.
        if (is_null($file_id)) {
            $this->filesCache = FileCollection::fromCollection($this->id, true);
;
            $fileCollectionCount = count($this->files);

            if (1 != $fileCollectionCount) {
                throw new TreeFileCollectionException($this, $fileCollectionCount);
            }
            $this->fileCache = reset($this->files)->file;
            $this->uuid = $this->files->uuid;
        }
    }

    /**
     * Set the info of a Collection, which may cause further processing
     * dependant on the collection's type
     * 
     * @param Collection $what the Collection instance who's info is being set
     * @param string $info specific information about this instance of a collection
     * 
     * @throws OverwriteCollectionException
     */
    protected function setInfo($pathInfo) {
        // Throw an error if attempting to change after already created.
        Logger::info('CollectionTree::setInfo');
        if ($this->info != null) {
           throw new OverwriteCollectionException($this, $info);
        }

        $this->info = $pathInfo;

        $this->fileCache = $this->transfer->addFile($pathInfo, 0, 'text/directory');
        $this->uuid = $this->fileCache->uuid;
        $this->addFile($fileCache);
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
    protected static function getProperty($c, $property) {
        if($property == 'uuid') {
            $c->loadTreeFile();
            return $c->uuid;
        }
        
        if($property == 'file') {
            $c->loadTreeFile();
            return $c->fileCache;
        }
        
        return parent::getProperty($c, $property);
    }
}

/**
 *  Represents a Collection of Files underneath a directory path,
 *  which belongs to a CollectionTree.
 */
class CollectionDirectory extends Collection
{
    /**
     * Override database Table to use
     */
    protected static $dataTable = 'Collections';
        
    /**
     * Set the info of a Collection, which may cause further processing
     * dependant on the collection's type
     * 
     * @param Collection $what the Collection instance who's info is being set
     * @param string $info specific information about this instance of a collection
     * 
     * @throws OverwriteCollectionException
     */
    protected function setInfo($pathInfo) {
        // Throw an error if attempting to change after already created.
        Logger::info('CollectionDirectory::setInfo');
        if ($this->info != null) {
           throw new OverwriteCollectionException($this, $info);
        }
            
        $parent_path = $pathInfo;
        $this->info = $pathInfo;
        $pos = strpos($pathInfo, '/');
      
        if (!($pos === false)) {
           $parent_path = substr($pathInfo, $pos);
        }

        Logger::info(get_called_class().' CREATE CollectionTree');
        $this->parentCache = $this->transfer->addCollection(CollectionType::$TREE, $parent_path);
        $this->parent_id = $this->parent->id;
    }
}
