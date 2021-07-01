<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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
 * Represents an user in database
 */
class AVResult extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        // synth id
        'id' => array(
            'type' => 'uint',
            'size' => 'big',
            'primary' => true,
            'autoinc' => true
        ),
        // file inspected
        'file_id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'null' => false,
            'primary' => true
        ),
        // the id of the avprogram that made this result
        'app_id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true
        ),
        // since the URL app might be used a few times it might want an explicit name
        'name' => array(
            'type' => 'string',
            'size' => 64,
            'null' => true,
        ),
        // goes the avprogram say this file is ok
        'passes' => array(
            'type' => 'bool',
            'default' => false,
            'null' => false
        ),
        // was there an error running the avprogram on this file
        'error' => array(
            'type' => 'bool',
            'default' => false,
            'null' => false
        ),
        // did the avprogram have something to say for the sys admin
        'internaldesc' => array(
            'type' => 'string',
            'size' => 255
        ),
        // When did this happen
        'created' => array(
            'type' => 'datetime',
            'primary' => true
        )
    );

    protected static $secondaryIndexMap = array();

    public static function getViewMap()
    {
        $constable = DBConstantAVProgram::getDBTable();
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select '.self::getDBTable().'.*, '.$constable.'.description as appname '
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable()
                        . ' join '.$constable.' on ' . self::getDBTable() . '.app_id = '.$constable.'.id '
            ;
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Set selectors
     */
    const FOR_FILE = ' file_id = :fileid  ORDER BY created DESC, id DESC';
    
    /**
     * Properties
     */
    protected $id = null;
    protected $file_id = null;
    protected $app_id = null;
    protected $name = null;
    protected $passes = null;
    protected $error = null;
    protected $internaldesc = null;
    protected $created = null;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of object to load from database (null if loading not wanted)
     * @param array $data data to create the object from (if already fetched from database)
     *
     * @throws AuditLogNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new AVResultNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Create a new av result
     *
     * @param DBOBject $file the file this AV result is for
     *
     * @return AVResult
     */
    public static function create($file, $app_id, $name, $passes, $error, $internaldesc = null)
    {
        $ret = new self();

        if( !$name ) {
            $name = DBConstantAVProgram::reverseLookup( $app_id );
        }
        
        $ret->file_id = $file->id;
        $ret->app_id = $app_id;
        $ret->name = $name;
        $ret->created = time();
        $ret->passes = $passes;
        $ret->error = $error;
        $ret->internaldesc = $internaldesc;
        
        $ret->save();

        if( !$file->have_avresults ) {
            $file->have_avresults = true;
            $file->save();
        }
        
        return $ret;
    }
    
    /**
     * Save in database
     */
    public function save()
    {
        $this->insertRecord($this->toDBData());
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
            'id',
            'file_id',
            'app_id',
            'name',
            'passes',
            'error',
            'internaldesc',
            'created',
         ))) {
            return $this->$property;
        }
        if ($property == 'app_name') {
            return DBConstantAVProgram::reverseLookup( $this->app_id );
        }
 
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Get AV Results related to a file
     *
     * @param DBObject $file The file to get records for
     *
     * @return array of AVResult
     */
    public static function forFile(DBObject $file, $event = null)
    {
        $ret = self::all(self::FOR_FILE, array('fileid' => $file->id));
        return $ret;
    }
    
    

    public static function cleanup()
    {
        $dbtype = Config::get('db_type'); 
    }

}
