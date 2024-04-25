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
 *  Represents path in the database
 */
class FileCollection extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        //primary key pair <collection_id,file_id>
        'collection_id' => array(
            'type' => 'uint',   //data type of 'id'
            'size' => 'big',
            'primary' => true,
        ),
        'file_id' => array(
            'type' => 'uint',   //data type of 'id'
            'size' => 'big',
            'primary' => true,
        ),
    );

    // the primary key pair<collection_id, file_id>
    // should implicitly create a secondary index on just
    // collection_id, but we also want to quickly sort on file_id
    protected static $secondaryIndexMap = array(
        'file_id' => array(
            'file_id' => array()
        )
    );
    
    /**
     * Properties
     */
    protected $collection_id = null;
    protected $file_id = null;
   
    /**
     * Related objects cache
     */
    private $collectionCache = null;
    private $fileCache = null;
    
    /**
     * Constructor
     *
     * @param integer $collection_id identifier of filepath to load from database (null if loading not wanted)
     * @param array $file_id data to create the filepath from (if already fetched from database)
     *
     * @throws FileCollectionNotFoundException
     */
    public function __construct($collection_id = null, $file_id = null, $data = null)
    {
        if (!is_null($collection_id) && !is_null($file_id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE collection_id = :collection_id AND file_id = :file_id');
            $statement->execute(array(':collection_id' => $collection_id,
                                      ':file_id' => $file_id));
            $data = $statement->fetch();
            if (!$data) {
                throw new FileCollectionNotFoundException('collection_id = '.$collection_id.', file_id = '.$file_id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Create a new FileCollection just to store the
     * referential ids. Actual loading/caching of the
     * Collection or File occurs when the __get(..)
     * method is called
     *
     * @param $collection_id the reference Collection's id
     * @param $file_id the reference File's id
     *
     * @return FileCollection
     */
    public static function create($collection_id, $file_id)
    {
        $filecollection = new self();
        
        $filecollection->collection_id = $collection_id;
        $filecollection->file_id = $file_id;

        return $filecollection;
    }

    /**
     * Add (create and save) a new FileCollection from existing
     * Collection and File objects. Note this is an expensive
     * DB operation because all three involved objects
     * (File, Collection, FileCollection) may have their save()
     * method called to guarentee existance and persistance of the
     * three object's $id.
     *
     * @param Collection $collection the reference Collection
     * @param File $file the reference File
     *
     * @return FileCollection
     */
    public static function add(Collection $collection, File $file)
    {
        // Need to guarentee $file->id and $collection->id are not null
        $filecollection = FileCollection::create($collection->id, $file->id);

        $filecollection->collectionCache = $collection;
        $filecollection->fileCache = $file;

        $filecollection->save();

        return $filecollection;
    }

    /**
     * Get Files belonging to a Collection. The returned <key,value> array is <file_id, FileCollection>
     *
     * @param Collection $collection the relater collection
     * @param bool full_load, default false, also fully loads the file
     * objects rather than lazy loading them if they are actually accessed.
     *
     * @return array of File
     */
    public static function fromCollection(Collection $collection, $full_load = null)
    {
        $owner_id = $collection->id;
        $files = array();
        
        if (!is_null($full_load)) {
            $s = DBI::prepare('SELECT * FROM '.File::getDBTable().' WHERE id IN (SELECT file_id FROM '.self::getDBTable().' WHERE collection_id = :collection_id)');
            $s->execute(array(':collection_id' => $owner_id));
            foreach ($s->fetchAll() as $data) {
                $file_id = $data['id'];
                $filecollection = self::create($owner_id, $file_id);
                $files[$file_id] = $filecollection;
                $filecollection->collectionCache = $collection;
                $filecollection->fileCache = File::fromData($file_id, $data);
            }
        } else {
            $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE collection_id = :collection_id');
            $s->execute(array(':collection_id' => $owner_id));
            foreach ($s->fetchAll() as $data) {
                $file_id = $data['file_id'];
                $filecollection = self::create($owner_id, $file_id);
                $files[$file_id] = $filecollection;
            }
        }
        return $files;
    }
    
    /**
     * Get FileCollection sets belonging to an array of Collection ids.
     *
     * @param $collectionIds - array of collection_id values, ie array[] = $collection_id
     *
     * @return 2d array[collection_id][file_id]=FileCollection
     */
    public static function fromCollectionIds($collectionIds)
    {
        $set = array();

        if( empty($collectionIds)) {
            return $set;
        }
        
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE collection_id IN ('.implode(", ", $collectionIds).') ORDER BY :order1, :order2');
        $s->execute(array(':order1' => 'collection_id',
                          ':order2' => 'file_id'));
        $current = null;
        $current_id = -1;
        foreach ($s->fetchAll() as $data) {
            $file_id = $data['file_id'];
            $collection_id = $data['collection_id'];

            if ($current_id != $collection_id) {
                if (!is_null($current)) {
                    $set[$current_id] = $current;
                }
                $current = array();
                $current_id = $collection_id;
            }
            $current[$file_id] = self::create($collection_id, $file_id);
        }
        if (!is_null($current)) {
            $set[$current_id] = $current;
        }
        return $set;
    }

    /**
     * Get the Collections that a File belongs to. The returned <key,value> array is <collection_id, FileCollection>
     *
     * @param File $file the relater file
     * @param bool full_load, default false, also fully loads the collection
     * objects rather than lazy loading them if they are actually accessed.
     *
     * @return array of Collection
     */
    public static function fromFile(File $file, $full_load = null)
    {
        $file_id = $file->id;
        $collections = array();
        
        if (!is_null($full_load)) {
            $s = DBI::prepare('SELECT * FROM '.Collection::getDBTable().' WHERE id IN (SELECT collection_id FROM '.self::getDBTable().' WHERE file_id = :file_id)');
            $s->execute(array(':file_id' => $file_id));
            foreach ($s->fetchAll() as $data) {
                $collection_id = $data['id'];
                $filecollection = self::create($collection_id, $file_id);
                $collections[$collection_id] = $filecollection;
                $filecollection->fileCache = $file;
                $filecollection->collectionCache = Collection::fromData($collection_id, $data);
            }
        } else {
            $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE file_id = :file_id');
            $s->execute(array(':file_id' => $file_id));
            foreach ($s->fetchAll() as $data) {
                $collection_id = $data['collection_id'];
                $filecollection = self::create($collection_id, $file_id);
                $collections[$collection_id] = $filecollection;
            }
        }
        return $collections;
    }
    
    /**
     * (Overload DBObject::save) Save into database
     */
    public function save()
    {
        $this->insertRecord($this->toDBData());
    }

    /**
     * Delete the filecollection
     */
    public function delete()
    {
        // Remove from database
        $s = DBI::prepare('DELETE FROM '.static::getDBTable().' WHERE collection_id = :collection_id AND file_id = :file_id');
        $s->execute(array(':collection_id' => $this->collection_id,
                          ':file_id' => $this->file_id));
    }

    public static function removeFile( $file )
    {
        $s = DBI::prepare('DELETE FROM '.static::getDBTable().' WHERE file_id = :file_id');
        $s->execute(array(':file_id' => $file->id));
    }   
    
    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @throws PropertyAccessException
     *
     * @return property name
     */
    public function __get($property)
    {
        if (in_array($property, array(
            'collection_id', 'file_id'
        ))) {
            return $this->$property;
        }
        
        if ($property == 'collection') {
            if (is_null($this->collectionCache)) {
                $this->collectionCache = Collection::fromId($this->collection_id);
            }
            return $this->collectionCache;
        }
        
        if ($property == 'file') {
            if (is_null($this->fileCache)) {
                $this->fileCache = File::fromId($this->file_id);
            }
            return $this->fileCache;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $file_id name to set property to
     *
     * @throws PropertyAccessException
     */
    public function __set($property, $file_id)
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
        return static::getClassName().'#'.$this->collection_id.':'.$this->file_id;
    }
}
