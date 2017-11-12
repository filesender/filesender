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
 *  Represents file in the database
 */
class File extends DBObject
{

    /**
     * Database map
     */
    protected static $dataMap = array(
        //file id, as in the database
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
        'uid' => array(
            'type' => 'string',
            'size' => 60
        ),
        'name' => array(
            'type' => 'string',
            'size' => 255,
        ),
        'mime_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'size' => array(
            'type' => 'uint',
            'size' => 'big'
        ),
        'encrypted_size' => array(
            'type' => 'uint',
            'null' => true,
            'size' => 'big'
        ),
        'upload_start' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'upload_end' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'sha1' => array(
            'type' => 'string',
            'size' => 40,
            'null' => true
        ),
        'storage_class_name' => array(
            'type' => 'string',
            'size' => 60,
            'null' => true,
            'default' => 'StorageFilesystem'
        )
    );

    protected static $secondaryIndexMap = array(
        'transfer_id' => array( 
            'transfer_id' => array()
        )
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $transfer_id = null;
    protected $uid = null;
    protected $name = null;
    protected $mime_type = null;
    protected $size = 0;
    protected $encrypted_size = 0;
    protected $upload_start = 0;
    protected $upload_end = 0;
    protected $sha1 = null;
   
    /**
     * Related objects cache
     */
    private $transferCache = null;
    private $logsCache = null;


    /**
     * Constructor
     * 
     * @param integer $id identifier of file to load from database (null if loading not wanted)
     * @param array $data data to create the file from (if already fetched from database)
     * 
     * @throws FileNotFoundException
     */
    public function __construct($id = null, $data = null) {
    
        $this->storage_class_name = Storage::getDefaultStorageClass();
        
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
     * Create a new file (for upload)
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return File
     */
    public static function create(Transfer $transfer) {
        $file = new self();
        
        // Init cache to empty to avoid db queries
        $file->logsCache = array();
        
        $file->transfer_id = $transfer->id;
        $file->transferCache = $transfer;
        
        // Generate uid until it is indeed unique
        $file->uid = Utilities::generateUID(function($uid, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.File::getDBTable().' WHERE uid = :uid');
            $statement->execute(array(':uid' => $uid));
            $data = $statement->fetch();
            if(!$data) Logger::info('File uid generation took '.$tries.' tries');
            return !$data;
        });

        $file->storage_class_name = Storage::getDefaultStorageClass();
        
        return $file;
    }
    
    /**
     * Delete the file
     */
    public function beforeDelete() {
        Storage::deleteFile($this);
        
        Logger::info($this.' deleted');
    }
    
    /**
     * Get file from uid
     * 
     * @param string $uid
     * 
     * @return File
     */
    public static function fromUid($uid) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE uid = :uid');
        $s->execute(array('uid' => $uid));
        $data = $s->fetch();
        
        if(!$data) throw FileNotFoundException('uid = '.$uid);
        
        return self::fromData($data['id'], $data); // Don't query twice, use loaded data
    }
    
    /**
     * Get files from Transfer
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return array of File
     */
    public static function fromTransfer(Transfer $transfer) {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id');
        $s->execute(array('transfer_id' => $transfer->id));
        $files = array();
        foreach($s->fetchAll() as $data) $files[$data['id']] = self::fromData($data['id'], $data); // Don't query twice, use loaded data
        return $files;
    }
    
    /**
     * Store a chunk at offset
     * 
     * @param mixed $chunk the chunk data (binary)
     * @param int $offset the chunk offset in the file, if null appends at end of file
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
     * End file upload
     */
    public function complete() {
        if($this->upload_end) return true; // Already completed
        
        $r = Storage::completeFile($this);
        
        $this->upload_end = time();
        $this->save();
        
        Logger::logActivity(LogEventTypes::FILE_UPLOADED, $this);
        Logger::info($this.' fully uploaded, took '.$this->upload_time.'s');
        
        return $r;
    }
    
    /**
     * Read a chunk at offset
     * 
     * @param int $offset the chunk offset in the file, if null reads next chunk (Storage keeps track of it)
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
            'id', 'transfer_id', 'uid', 'name', 'mime_type', 'size', 'encrypted_size', 'upload_start', 'upload_end', 'sha1', 'storage_class_name'
        ))) return $this->$property;
        
        if($property == 'transfer') {
            if(is_null($this->transferCache)) $this->transferCache = Transfer::fromId($this->transfer_id);
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
     * @throws FileBadHashException
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
            if(!preg_match('`^[0-9a-f]{40}$`', $value)) throw new FileBadHashException($this, $value);
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
