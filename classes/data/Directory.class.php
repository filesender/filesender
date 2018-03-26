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
 *  Represents path in the database
 */
class Directory extends DBObject
{

    /**
     * Database map
     */
    protected static $dataMap = array(
        //directory id, as in the database
        'id' => array(
            'type' => 'uint',   //data type of 'id'
            'size' => 'medium', //size of the integer stored in 'id' (in bytes, or otherwise)
            'primary' => true,  //indicates that 'id' is the primary key in the DB
            'autoinc' => true,   //indicates that 'id' is auto-incremented
        ),
        'root_id' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'path' => array(
            'type' => 'string',
            'size' => 2048,
        )
    );

    protected static $secondaryIndexMap = array(
        'root_id' => array( 
            'root_id' => array()
        )
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $root_id = null;
    protected $path = null;
   
    /**
     * Related objects cache
     */
    private $rootCache = null;

    /**
     * Constructor
     * 
     * @param integer $id identifier of directory to load from database (null if loading not wanted)
     * @param array $data data to create the directory from (if already fetched from database)
     * 
     * @throws FileNotFoundException
     */
    public function __construct($id = null, $data = null) {
    
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new FileNotFoundException('id = '.$id);
        }

        // Fill properties from provided data
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Create a new Directory (for upload)
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return Directory
     */
    public static function create(Transfer $transfer, string $path) {
        $directory = new self();
        
        $pos = strpos($path, '/');
        $directory->$path = $path;
        $root = $path;
      
        if (!($pos === false)) {
           $root = substr($path, $pos - 1);
        }

        $rootCache = File::createTree($transfer, $root);
        $this->root_id = $rootCache->__get('id');

        return $directory;
    }
    
    /**
     * Delete the directory
     */
    public function beforeDelete() {
        Storage::deleteDirectory($this);
        
        Logger::info($this.' deleted');
    }
    
    /**
     * Get directory from uid
     * 
     * @param string $uid
     * 
     * @return Directory
     */
    public static function fromUid($uid) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE uid = :uid');
        $s->execute(array('uid' => $uid));
        $data = $s->fetch();
        
        if(!$data) throw DirectoryNotFoundException('uid = '.$uid);
        
        return self::fromData($data['id'], $data); // Don't query twice, use loaded data
    }
    
    /**
     * Get files from Transfer
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return array of Directory
     */
    public static function fromTransfer(Transfer $transfer) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE root_id = :root_id');
        $s->execute(array('root_id' => $transfer->id));
        $files = array();
        foreach($s->fetchAll() as $data) $files[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $files;
    }
    
    /**
     * Store a chunk at offset
     * 
     * @param mixed $chunk the chunk data (binary)
     * @param int $offset the chunk offset in the directory, if null appends at end of directory
     */
    public function writeChunk($chunk, $offset = null) {
        if(!$this->upload_start) {
            $this->upload_start = time();
            $this->save();
        }
        
        $res = Storage::writeChunk($this, $chunk, $offset);
        
        Logger::info($this.' chunk['.((int)$offset).'..'.((int)$offset + strlen($chunk)).'] written'.(Auth::isGuest() ? ' by '.AuthGuest::getGuest() : ''));
        
        return $res;
    }
    
    /**
     * End directory upload
     */
    public function complete() {
        if($this->upload_end) return true; // Already completed
        
        $r = Storage::completeDirectory($this);
        
        $this->upload_end = time();
        $this->save();
        
        Logger::logActivity(LogEventTypes::DIRECTORY_UPLOADED, $this);
        Logger::info($this.' fully uploaded, took '.$this->upload_time.'s');
        
        return $r;
    }
    
    /**
     * Read a chunk at offset
     * 
     * @param int $offset the chunk offset in the directory, if null reads next chunk (Storage keeps track of it)
     * @param int $length the chunk length, if null will use download_chunk_size from config
     * 
     * @return mixed chunk data or null if no more data is available
     */
    public function readChunk($offset = null, $length = null) {
        return Storage::readChunk($this, $offset, $length);
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
            'id', 'root_id', 'uid', 'name', 'mime_type', 'size', 'encrypted_size', 'upload_start', 'upload_end', 'sha1', 'filedirectory_id', 'storage_class_name'
        ))) return $this->$property;
        
        if($property == 'transfer') {
            if(is_null($this->transferCache)) $this->transferCache = Transfer::fromId($this->root_id);
            return $this->transferCache;
        }
        
        if($property == 'owner') {
            return $this->transfer->owner;
        }
        
        if($property == 'auditlogs') {
            if(is_null($this->logsCache)) $this->logsCache = AuditLog::fromTarget($this);
            return $this->logsCache;
        }
        
        if($property == 'downloads') {
            return array_filter($this->auditlogs, function($log) {
                return $log->event == LogEventTypes::DOWNLOAD_ENDED;
            });
        }
        
        if($property == 'upload_time') {
            if(!$this->upload_start || !$this->upload_end)
                return null;
            
            return $this->upload_end - $this->upload_start;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     * 
     * @param string $property property to get
     * @param mixed $value value to set property to
     * 
     * @throws DirectoryBadHashException
     * @throws PropertyAccessException
     */
    public function __set($property, $value) {
        if($property == 'name') {
            $this->name = (string)$value;
        }else if($property == 'auditlogs') {
            $this->logsCache = (array)$value;
        }else if($property == 'mime_type') {
            $this->mime_type = (string)$value;
        }else if($property == 'size') {
            $this->size = (int)$value;
        }else if($property == 'encrypted_size') {
            $this->encrypted_size = (int)$value;
        }else if($property == 'sha1') {
            if(!preg_match('`^[0-9a-f]{40}$`', $value)) throw new DirectoryBadHashException($this, $value);
            $this->sha1 = (string)$value;
        }else if($property == 'storage_class_name') {
            $this->storage_class_name = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.', '.$this->size.' bytes)';
    }

    public function getStream() {
        return Storage::getStream($this);
    }

}
