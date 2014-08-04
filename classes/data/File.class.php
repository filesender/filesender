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
        'size' => array(
            'type' => 'uint',
            'size' => 'big'
        ),
        'sha1' => array(
            'type' => 'string',
            'size' => 40,
            'null' => true
        )
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $transfer_id = null;
    protected $uid = null;
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
        
        $file->transfer_id = $transfer->id;
        $file->uid = Utilities::generateUID();
        
        return $file;
    }
    
    /**
     * Save file in database
     */
    public function save() {
        if($this->id) {
            $this->updateRecord($this->toDBData(), 'id');
        }else{
            $this->insertRecord($this->toDBData());
            $this->id = DBI::lastInsertId();
        }
    }
    
    /**
     * Delete the file
     */
    public function delete() {
        Storage::delete($this);
        
        $s = DBI::prepare('DELETE FROM '.self::getDBTable().' WHERE id = :id');
        $s->execute(array('id' => $this->id));
    }
    
    /**
     * Get files from Transfer
     * 
     * @param object $transfer the relater transfer
     * 
     * @return array file list
     */
    public static function fromTransfer($transfer) {
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
     * @param int $offset the chunk offset in the file
     */
    public function writeChunk($chunk, $offset) {
        Storage::writeChunk($this, $chunk, $offset);
    }
    
    /**
     * Read a chunk at offset
     * 
     * @param int $offset the chunk offset in the file
     * 
     * @return mixed chunk data or null if no more data is available
     */
    public function readChunk($offset) {
        return Storage::readChunk($this, $offset);
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
            'id', 'transfer_id', 'uid', 'name', 'size', 'sha1'
        ))) return $this->$property;
        
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
        }else if($property == 'size') {
            $this->size = (int)$value;
        }else if($property == 'sha1') {
            if(!preg_match('`^[0-9a-f]{40}$`', $value)) throw new FileBadHashException($value);
            $this->sha1 = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
}
