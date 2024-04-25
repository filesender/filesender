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
class ShredFile extends DBObject
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
        'name' => array(
            'type' => 'string',
            'size' => 60
        ),
        'errormessage' => array(
            'type' => 'string',
            'size' => 300,
            'null' => true
        ),
    );


    /**
     * Properties
     */
    protected $id   = null;
    protected $name = null;
    protected $errormessage = null;
   

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


    public static function getShredPath()
    {
        $path = Config::get('storage_filesystem_shred_path');
        if (!$path) {
            throw new ConfigMissingParameterException('storage_filesystem_shred_path');
        }
        
        // Check if storage path exists and is writable
        if (!is_dir($path) || !is_writable($path)) {
            throw new ConfigMissingParameterException('storage_filesystem_shred_path');
        }
        
        // Build final path and cache
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        return $path;
    }

    /**
     * Create a new junk file
     *
     * @param original_path the file path to move to the junk area for shredding
     *
     * @return ShredFile
     */
    public static function create($original_path)
    {
        $file = new self();

        $shredpath = self::getShredPath();

        if (!is_dir($shredpath)) {
            throw new StorageFilesystemCannotDeleteException($original_path, $file);
        }

        // Generate uid until it is indeed unique
        $file->name = Utilities::generateUID(true, function ($uid, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.File::getDBTable().' WHERE uid = :uid');
            $statement->execute(array(':uid' => $uid));
            $data = $statement->fetch();
            if (!$data) {
                Logger::info('File uid generation took '.$tries.' tries');
            }
            return !$data;
        });

        if (!rename($original_path, $shredpath.$file->name)) {
            throw new StorageFilesystemCannotDeleteException($original_path, $file);
        }

        return $file;
    }

    /**
     * Shred the file on disk
     */
    public function shred()
    {
        $ret = true;
        $shredpath = self::getShredPath();
        $path = $shredpath.$this->name;

        $cmd = Config::get('storage_filesystem_file_shred_command');
        $cmd = str_replace('{path}', escapeshellarg($path), $cmd);
        exec($cmd, $out, $ret);

        if ($ret) {
            $this->errormessage = substr(implode("\n", $out), 0, 300);
            $this->save();
            $ret = false;
        } else {
            // remove ourselves from database, we are done.
            $this->delete();
        }

        return $ret;
    }

    /**
     * Get file from uid
     *
     * @param string $uid
     *
     * @return File
     */
    public static function fromName($name)
    {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE name = :name');
        $s->execute(array(':name' => $name));
        $data = $s->fetch();
        
        if (!$data) {
            throw FileNotFoundException('name = '.$name);
        }
        
        return self::fromData($data['id'], $data); // Don't query twice, use loaded data
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
            'id', 'name'
        ))) {
            return $this->$property;
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
            $this->name = (string)$value;
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
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.')';
    }


    public static function shouldUseShredFile()
    {
        $cmd = Config::get('storage_filesystem_file_shred_command');
        return $cmd && strlen($cmd) > 0;
    }
}
