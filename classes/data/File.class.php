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
 *  Represents a record in the files table, 
 *
 *  @property array $dataMap: Files DB table structure
 *  Note: Separate out the system specifics (path, etc.) - an abstraction layer between the File class (with is tied a to Transaction/Transfer) and the specific file system (could write a file that interfaces Amazon S3 or other service).
 *  File system specific class , FileAccess / Hardlink class, would also contain methods to add and read chunk from offset ... 
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
        'transferid' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'name' => array(
            'type' => 'string',
            'size' => 500,
        ),
        'size' => array(
            'type' => 'ulong',
            'size' => 64,
        ),
        'sha1' => array(
            'type' => 'string',
            'size' => 500,
        )
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $transferid = null;
    protected $name = null;
    protected $size = 0;
    protected $sha1 = null;
   
    /**
     * Constructor
     * 
     * @param integer $id identifier of file to load from database (null if loading not wanted)
     * @param array $data data to create the file from (if already fetched from database)
     * 
     * @throws FileNotFoundException
     */
    public function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new FileNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Create a new file (for upload)
     * 
     * @param object $transfer the relater transfer
     * 
     * @return object file
     */
    public static function create($transfer) {
        $file = new self();
        
        $file->transferid = $transfer->id;
        
        return $file;
    }
    
    /**
     * Store a chunk at offset
     * 
     * @param mixed $chunk the chunk data (binary)
     * @param int $offset the chunk offset in the file
     */
    public function storeChunk($chunk, $offset) {
        FileStorage::storeChunk($this, $chunk, $offset);
    }
    
    /**
     * Read a chunk at offset
     * 
     * @param int $offset the chunk offset in the file
     * 
     * @return mixed chunk data or null if no more data is available
     */
    public function readChunk($offset) {
        return FileStorage::readChunk($this, $offset);
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
            'id', 'transferid', 'name', 'size', 'sha1'
        ))) return $this->$property;
        
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
        if($property == 'name') {
            $this->name = (string)$value;
        }else if($property == 'size') {
            $this->size = (int)$value;
        }else if($property == 'sha1') {
            if(!preg_match('`^[0-9a-f]{40}$`', $value)) throw new FileBadHashException($value);
            $this->sha1 = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
}
