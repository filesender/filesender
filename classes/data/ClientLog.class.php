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
 * Represents a client log entry in database
 */
class ClientLog extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'big',
            'primary' => true,
            'autoinc' => true
        ),
        'userid' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'message' => array(
            'type' => 'text'
        )
    );

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    protected static $secondaryIndexMap = array(
        'userid' => array(
            'userid' => array()
        ),
        'created_and_userid' => array(
            'created' => array(),
            'userid' => array()
        )
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $userid = null;
    protected $created = 0;
    protected $message = null;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of transfer to load from database (null if loading not wanted)
     * @param array $data data to create the transfer from (if already fetched from database)
     *
     * @throws TransferNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            /** @var PDOStatement $statement */
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new TransferNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Get client logs from user
     *
     * @param mixed $user User or user id
     *
     * @return ClientLog[]
     */
    public static function fromUser($user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        return self::all('userid = :userid ORDER BY id ASC', array(':userid' => $user));
    }
    
    /**
     * Create a new client log
     *
     * @param User $user
     * @param string $message
     *
     * @return self
     */
    public static function create(User $user, $message)
    {
        $log = new self();
        $log->userid = $user->id;
        $log->message = $message;
        $log->created = time();
        
        return $log;
    }
    
    /**
     * Stash logs
     *
     * @param User $user
     * @param string[] $logs
     *
     * @return self[]
     */
    public static function stash(User $user, $logs)
    {
        $stash = self::fromUser($user);
        $len = self::stashSize();
        while ($stash && (count($stash) + count($logs) > $len)) {
            $log = array_shift($stash);
            $log->delete();
        }
    
        while (count($logs) > $len) { // stash is empty if this runs
            array_shift($logs);
        }
    
        foreach ($logs as $message) {
            $log = self::create($user, $message);
            $log->save();
            $stash[] = $log;
        }
        
        return $stash;
    }
    
    /**
     * Get stash size
     *
     * @return int
     */
    public static function stashSize()
    {
        return Config::get('clientlogs_stashsize');
    }
    
    /**
     * Clean old entries
     */
    public static function clean()
    {
        $days = Config::get('clientlogs_lifetime');
        
        /** @var PDOStatement $statement */
        $statement = DBI::prepare('DELETE FROM '.self::getDBTable().' WHERE created < :date');
        $statement->execute(array(':date' => date('Y-m-d', time() - $days * 86400)));
    }
    
    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @throws PropertyAccessException
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (in_array($property, array(
            'id', 'userid', 'message', 'created'
        ))) {
            return $this->$property;
        }
        
        if ($property == 'user' || $property == 'owner') {
            return User::fromId($this->userid);
        }
        
        throw new PropertyAccessException($this, $property);
    }

    /**
     * Perform reasonably fast validation of config options.
     * This allows the global config loader to check with many classes
     * by calling class::validateConfig() so that particular pages do not
     * have to be loaded to find configuration issues.
     */
    public static function validateConfig( $allowSlowerTests = false )
    {
        $days = Config::get('clientlogs_lifetime');
        if (!$days || !is_int($days) || $days <= 0) {
            throw new ConfigBadParameterException('clientlogs_lifetime must be a positive integer');
        }

        $size = Config::get('clientlogs_stashsize');
        if (!is_int($size) || ($size <= 0)) {
            throw new ConfigBadParameterException('clientlogs_stashsize must be a positive integer');
        }

        if( $allowSlowerTests ) {
        }
    }

    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     *
     * @throws PropertyAccessException
     */
    public function __set($property, $value)
    {
        throw new PropertyAccessException($this, $property);
    }
}
