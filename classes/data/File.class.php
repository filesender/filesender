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
            'size' => 'big',    //size of the integer stored in 'id' (in bytes, or otherwise)
            'primary' => true,  //indicates that 'id' is the primary key in the DB
            'autoinc' => true,   //indicates that 'id' is auto-incremented
        ),
        'transfer_id' => array(
            'type' => 'uint',
            'size' => 'big',
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
        ),
        // The IV used to encrypt the file.
        // This is 24 bytes long to allow for storage
        // of a 128bit array in base64 format
        'iv' => array(
            'type' => 'string',
            'size' => 24,
            'null' => true
        ),
        // For encrypted files and encryption algorithms that
        // support AEAD this is the string that the client sent
        // and is needed during decryption.
        //
        // This is base64 encoded by the client because we, as the server,
        // can not really use it for much. After a base64 decoude it should
        // be a JSON object which uses the aeadversion field to version itself.
        // Note that it is encoded manually into JSON on the client side
        // so that it is a canonical representation
        'aead' => array(
            'type' => 'string',
            'size' => 512,
            'null' => true
        ),

        // this is a cache of which files have no avresults
        'have_avresults' => array(
            'type' => 'bool',
            'default' => false
        )
    );

    protected static $secondaryIndexMap = array(
        'transfer_id' => array(
            'transfer_id' => array()
        )
    );


    public static function getViewMap()
    {
        $a = array();
        $filesbywhodef = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'upload_start')
                        . DBView::columnDefinition_age($dbtype, 'upload_end')
                        . '  from ' . self::getDBTable();

            // This view exists only to allow mariadb 10.0 and older database
            // to work where they do not allow subqueries in view definitions.
            $transferviewdef[$dbtype] = Transfer::getPrimaryViewDefinition($dbtype);

            $filesbywhodef[$dbtype] = 'select t.id as transferid,name,upload_end,f.id as fileid,mime_type,size,'
                                . ' t.* from '.self::getDBTable().' f, '
                                    . ' filestranferviewcopy t '
                                    . ' where f.transfer_id = t.id order by t.id';
        }
        
        
        return array( strtolower(self::getDBTable()) . 'view' => $a ,
                      'filestranferviewcopy' => $transferviewdef    ,
                      'filesbywhoview'       => $filesbywhodef
        );
    }
    
    

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
    protected $iv = '';
    protected $aead = null;
    protected $have_avresults = false;
    protected $storage_class_name = ''; // set in constructor
   
    /**
     * Related objects cache
     */
    private $transferCache = null;
    private $logsCache = null;
    private $pathCache = null;
    private $directoryCache = null;

    /**
     * Set selectors
     */
    const WITHOUT_AVRESULTS = " have_avresults = false ";
    
    /**
     * Set the name of a File, optionally creating a Path
     * object internally if pathedName contains slashes
     *
     * @param string $pathName a potentially fully pathed name for the File
     */
    protected function setName($pathName)
    {
        if (is_null($pathName)) {
            return;
        }
        $this->name = $pathName;
        $pos = strrpos($pathName, '/');
      
        // If the name for this file appears to be a path because it
        // contains a '/' (slash), add the file to a CollectionDirectory
        if (!($pos === false)) {
            CollectionType::initialize();
            $this->name = substr($pathName, $pos + 1);
            $this->pathCache = $pathName;
            $this->directoryCache = $this->transferCache->addCollection(CollectionType::$DIRECTORY, substr($pathName, 0, $pos));
            $this->directoryCache->addFile($this);
        }
    }

    /**
     * Get a File's full path, which may just be the File's name
     * if it does not belong to a CollectionDirectory
     *
     * @return string the File's full path
     *
     * @throws FileMultiplePathException
     */
    protected function loadDirectoryPath()
    {
        // Taking advantage of two $pathCache states:
        // 1. null -> need to see if File belongs to a CollectionDirectory
        // 2. !null -> we have checked whether a FIle belongs to a CollectionDirectory
        if (isset($this->pathCache)) {
            return $this->pathCache;
        }
        // Default that the path is just the name
        $this->pathCache = $this->name;
        $collections = $this->transfer->collections;

        if (is_null($collections) ||
            count($collections) < 1 ||
            $this->mime_type === 'text/directory') {
            return $this->pathCache;
        }
        
        foreach ($collections as $collection_type_id => $directories) {
            if ($collection_type_id != CollectionType::DIRECTORY_ID) {
                continue;
            }
            foreach ($directories as $dir) {
                if (array_key_exists($this->id, $dir->files)) {
                    $this->pathCache = $dir->info.'/'.$this->name;
                    $this->directoryCache = $dir;
                    break;
                }
            }
        }
        
        return $this->pathCache;
    }
    
    /**
     * Calculate the encrypted file size
     *
     * @return int What $file->encrypted_size should be for this file.
     */
    private function calculateEncryptedFileSize()
    {
        $upload_chunk_size = Config::get('upload_chunk_size');
        
        $echunkdiff = Config::get('upload_crypted_chunk_size') - $upload_chunk_size;
        $chunksMinusOne = ceil($this->size / $upload_chunk_size)-1;
        $lastChunkSize = $this->size - ($chunksMinusOne * $upload_chunk_size);

        // padding on the last chunk of the file
        // may not be a full chunk so need to calculate
        $lastChunkPadding = 16 - $lastChunkSize % 16;
        if ($lastChunkPadding == 0) {
            $lastChunkPadding = 16;
        }

        switch( $this->transfer->key_version ) {
            case CryptoAppConstants::v2018_importKey_deriveKey:
            case CryptoAppConstants::v2017_digest_importKey:
                return $this->size + ($chunksMinusOne * $echunkdiff) + $lastChunkPadding + 16;
            case CryptoAppConstants::v2019_gcm_importKey_deriveKey:
            case CryptoAppConstants::v2019_gcm_digest_importKey:
                return $this->size + (($chunksMinusOne+1) * $echunkdiff);
            default:
        }
        // fall through is an error
        throw new BadCryptoKeyVersionException( $this->transfer->key_version );
    }

    /**
     * Constructor
     *
     * @param integer $id identifier of file to load from database (null if loading not wanted)
     * @param array $data data to create the file from (if already fetched from database)
     *
     * @throws FileNotFoundException
     */
    public function __construct($id = null, $data = null)
    {
        $this->storage_class_name = Storage::getDefaultStorageClass();
        
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new FileNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Create a file (for upload)
     *
     * @param Transfer $transfer the relater transfer
     * @param string $name the file name
     * @param string $size the file size
     * @param string $mime_type the optional file mime_type
     *
     * @return File
     */
    public static function create(Transfer $transfer, $name = null, $size = null, $mime_type = null)
    {
        $file = new self();
        
        // Init cache to empty to avoid db queries
        $file->logsCache = array();
        $file->transfer_id = $transfer->id;
        $file->transferCache = $transfer;

        // Generate timestamped uid until it is indeed unique
        $file->uid = Utilities::generateUID(true, function ($uid, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.File::getDBTable().' WHERE uid = :uid');
            $statement->execute(array(':uid' => $uid));
            $data = $statement->fetch();
            if (!$data) {
                Logger::info('File uid generation took '.$tries.' tries');
            }
            return !$data;
        });
        
        $file->storage_class_name = Storage::getDefaultStorageClass();

        if (!is_null($size)) {
            $file->size = $size;
            $file->encrypted_size = $file->calculateEncryptedFileSize();
        }
        $file->mime_type = $mime_type ? $mime_type : 'application/binary';
        $file->setName($name);
        
        return $file;
    }
    
    /**
     * Delete the file
     */
    public function beforeDelete()
    {
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
    public static function fromUid($uid)
    {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE uid = :uid');
        $s->execute(array(':uid' => $uid));
        $data = $s->fetch();
        
        if (!$data) {
            throw FileNotFoundException('uid = '.$uid);
        }
        
        return self::fromData($data['id'], $data); // Don't query twice, use loaded data
    }
    
    /**
     * Get files from Transfer
     *
     * @param Transfer $transfer the relater transfer
     *
     * @return array of File
     */
    public static function fromTransfer(Transfer $transfer)
    {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id order by name desc');
        $s->execute(array(':transfer_id' => $transfer->id));
        $tree_files = array();
        $files = array();
        foreach ($s->fetchAll() as $data) {
            $file = self::fromData($data['id'], $data); // Don't query twice, use loaded dat
            // mirror loadDirectoryPath(): Default that path and filename are the same
            $file->pathCache = $file->name;
            if ($file->size == 0 &&
                $file->mime_type === CollectionTree::FILE_MIME_TYPE) {
                $tree_files[$data['id']] = $file;
            } else {
                $files[$data['id']] = $file;
            }
        }

        $collections = $transfer->collections;

        if (!is_null($collections) && array_key_exists(CollectionType::DIRECTORY_ID, $collections)) {
            $directories = $collections[CollectionType::DIRECTORY_ID];
            // Set path info if it exists
            $t = DBI::prepare('SELECT fc.file_id AS id, c.info AS dirpath, c.id as dir_id FROM FileCollections fc, Collections c WHERE c.transfer_id = :transfer_id AND c.type_id = :collection_type AND fc.collection_id = c.id');
            $t->execute(array(':transfer_id' => $transfer->id,
                              ':collection_type' => CollectionType::DIRECTORY_ID));
            foreach ($t->fetchAll() as $data) {
                $file = $files[$data['id']];
                $file->pathCache = $data['dirpath'].'/'.$file->name;
                $file->directoryCache = $directories[$data['dir_id']];
            }
        }
        return $files;
    }
    
    /**
     * Store a chunk at offset
     *
     * @param mixed $chunk the chunk data (binary)
     * @param int $offset the chunk offset in the file, if null appends at end of file
     */
    public function writeChunk($chunk, $offset = null)
    {
        if (!$this->upload_start) {
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
    public function complete()
    {
        if ($this->upload_end) {
            return true;
        } // Already completed
        
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
    public function readChunk($offset = null, $length = null)
    {
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
    public function __get($property)
    {
        if (in_array($property, array(
            'transfer_id', 'uid', 'name', 'mime_type', 'size', 'encrypted_size', 'upload_start', 'upload_end', 'sha1'
          , 'storage_class_name', 'iv', 'aead', 'have_avresults'
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
        
        if ($property == 'path') {
            if (is_null($this->pathCache)) {
                $this->loadDirectoryPath();
            }
            return $this->pathCache;
        }
        
        if ($property == 'directory') {
            // $pathCache controls if $directoryCache was initialized.
            if (is_null($this->pathCache)) {
                $this->loadDirectoryPath();
            }
            return $this->directoryCache;
        }
        
        if ($property == 'owner') {
            return $this->transfer->owner;
        }
        
        if ($property == 'auditlogs') {
            if (is_null($this->logsCache)) {
                $this->logsCache = AuditLog::fromTarget($this);
            }
            return $this->logsCache;
        }
        
        if ($property == 'downloads') {
            return array_filter($this->auditlogs, function ($log) {
                return $log->event == LogEventTypes::DOWNLOAD_ENDED;
            });
        }
        
        if ($property == 'upload_time') {
            if (!$this->upload_start || !$this->upload_end) {
                return null;
            }
            
            return $this->upload_end - $this->upload_start;
        }

        if ($property == 'is_encrypted') {
            return $this->transfer->is_encrypted;
        }
        if ($property == 'scan_results') {
            return AVResult::forFile( $this );
        }
        if ($property == 'av_all_good') {
            $r = AVResult::forFile( $this );
            foreach($r as $res) {
                if( !$res->passes ) {
                    return false;
                }
            }
            return true;
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
    public function __set($property, $value)
    {
        if ($property == 'name') {
            $this->setName((string)$value);
        } elseif ($property == 'auditlogs') {
            $this->logsCache = (array)$value;
        } elseif ($property == 'mime_type') {
            $this->mime_type = (string)$value;
        } elseif ($property == 'size') {
            $this->size = (int)$value;
        } elseif ($property == 'encrypted_size') {
            $this->encrypted_size = (int)$value;
        } elseif ($property == 'sha1') {
            if (!preg_match('`^[0-9a-f]{40}$`', $value)) {
                throw new FileBadHashException($this, $value);
            }
            $this->sha1 = (string)$value;
        } elseif ($property == 'storage_class_name') {
            $this->storage_class_name = (string)$value;
        } elseif ($property == 'iv') {
            $this->iv = $value;
        } elseif ($property == 'aead') {
            $this->aead = $value;
        } elseif ($property == 'have_avresults') {
            $this->have_avresults = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
    
    /**
     * String caster
     *
     * @return string
     */
    public function __toString()
    {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.', '.$this->size.' bytes)';
    }

    public function getStream()
    {
        return Storage::getStream($this);
    }

    public static function findFilesWithoutAVResults( $limit = 100 )
    {
        return self::all( array('where' => self::WITHOUT_AVRESULTS
                               ,'limit' => $limit
        ));
        
    }
}
