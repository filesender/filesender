<?php
/* File : classes/File.class.php

 *** Copyright stuff here ***

Represents a record in the files table, 
provides transaction access or (magic getter),
creator from transaction, 
filename and size,

Separate out the system specifics (path, etc.) - an abstraction layer between the File class (with is tied a to Transaction/Transfer) and the specific file system (could write a file that interfaces Amazon S3 or other service).
FileAccess class:
methods to add and read chunk from offset ... 
*/

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

// include base Database Object class which we'll extend
require_once FILESENDER_BASE.'/classes/DBObject.class.php';

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

