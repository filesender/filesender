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
class Namespace extends DBObject
{

    /**
     * Database map
     */
    protected static $dataMap = array(
        //filepath id, as in the database
        'id' => array(
            'type' => 'uint',   //data type of 'id'
            'size' => 'medium', //size of the integer stored in 'id' (in bytes, or otherwise)
            'primary' => true,  //indicates that 'id' is the primary key in the DB
            'autoinc' => true,   //indicates that 'id' is auto-incremented
        ),
        'name' => array(
            'type' => 'string',
            'size' => 2048,
        )
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $name = null;
   
    /**
     * Constructor
     * 
     * @param integer $id identifier of filepath to load from database (null if loading not wanted)
     * @param array $data data to create the filepath from (if already fetched from database)
     * 
     * @throws NamespaceNotFoundException
     */
    public function __construct($id = null, $data = null) {
    
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new NamespaceNotFoundException('id = '.$id);
        }

        // Fill properties from provided data
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Create a new Namespace (for upload)
     * 
     * @param Transfer $transfer the relater transfer
     * 
     * @return Namespace
     */
    public static function create(Transfer $transfer, string $name) {
        $filepath = new self();
        
        $pos = strpos($name, '/');
        $filepath->$name = $name;
        $root = $name;
      
        if (!($pos === false)) {
           $root = substr($name, $pos - 1);
        }

        $rootCache = File::createTree($transfer, $root);
        $this->root_id = $rootCache->__get('id');

        return $filepath;
    }
    
    /**
     * Delete the filepath
     */
    public function beforeDelete() {
        Storage::deleteNamespace($this);
        
        Logger::info($this.' deleted');
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
    public function __get($property) {
        if(in_array($property, array(
            'id', 'name'
        ))) return $this->$property;
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     * 
     * @param string $property property to get
     * @param mixed $name name to set property to
     * 
     * @throws PropertyAccessException
     */
    public function __set($property, $name) {
        if($property == 'name') {
            $this->name = (string)$name;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->name.', '.strlen($this->name).' bytes)';
    }
}
