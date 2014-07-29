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

// Use database object superclass
if (substr(dirname(__FILE__), -3) ==  "new")                        //just for development
    require_once FILESENDER_BASE.'/classes/new/DBObject.class.php';
else
    require_once FILESENDER_BASE.'/classes/DBObject.class.php';

/**
 *  Represents a record in the files table, 
 *
 *  @property array $dataMap: Files DB table structure
 *  Note: Separate out the system specifics (path, etc.) - an abstraction layer between the File class (with is tied a to Transaction/Transfer) and the specific file system (could write a file that interfaces Amazon S3 or other service).
 *  File system specific class , FileAccess / Hardlink class, would also contain methods to add and read chunk from offset ... 
 */
class File extends DBObject
{
    /** Database map */
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
     * Declaring and initializing properties
     */
    protected $id = null;
    protected $transferid = null;
    protected $name = null;
    protected $size = 0;
    protected $sha1 = null;
    protected $hardlink = null;     // for holding an actual filesystem file
    protected $properties = array();
   
    public function __construct($id = null, $data = null)
    {
        parent::__construct($uid = null, $data = null);
        $this->properties = array(
            'id', 'transferid', 'name', 'size', 'sha1', 'hardlink'
        );
    }

    /**
     *  Magic property getter
     *
     *  @param string $property: name of property to get value of
     *  @throws PropertyAccessException
     */
    public function __get($property) 
    {
        if (in_array($property, $this->properties)) {
            return $this->$property;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
    

    /**
     *  Magic property setter
     *
     *  @param string $property: the property name
     *  @param mixed $value: value to set property to
     *  @throws PropertyAccessException
     *
     */
    public function __set($property, $value)
    {
        if (in_array($property, $this->properties)) {
            $this->property = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
}

