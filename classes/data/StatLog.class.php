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
if(!defined('FILESENDER_BASE')) die('Missing environment');

/**
 * Represents an user in database
 */
class StatLog extends DBObject {
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => true
        ),
        'event' => array(
            'type' => 'string',
            'size' => 32,
        ),
        'target_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'size' => array(
            'type' => 'int',
            'size' => 'big',
        ),
        
        'created' => array(
            'type' => 'datetime'
        )
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $event = null;
    protected $created = null;
    protected $target_type = null;
    protected $size = null;
    
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the user from (if already fetched from database)
     * 
     * @throws UserNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new StatLogNotFoundException('id = '.$id);
        }

        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Save in database
     */
    public function save() {
        $this->insertRecord($this->toDBData());
    }
    
    /**
     * Create a new stat log
     * 
     * @param StatEvent $event: the event to be logged
     * @param DBObject: the target to be logged
     * 
     * @return StatLog auditlog
     */
    public static function create($event, DBObject $target) {
        $lt = Config::get('statlog_lifetime');
        if(is_null($lt) || (is_bool($lt) && !$lt)) return; // statlog disabled
        
        if(!LogEventTypes::isValidValue($event))
            throw new StatLogUnknownEventException($event);
        
        $log = new self();
        $log->event = $event;
        $log->created = time();
        $log->target_type = get_class($target);
        
        switch ($log->target_type){
            case File::getClassName():
                $log->size = $target->size;
                break;
            
            case Transfer::getClassName():
                $log->size = $target->size;
                break;
            
            default:
                $log->size = 0;
                break;
        }
        
        $log->save();
        
        return $log;
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
            'id', 
            'event',
            'created',
            'target_type',
            'size',
        ))) return $this->$property;
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Clean logs
     */
    public static function clean() {
        Logger::info('Cleaning statlogs');
        $lt = Config::get('statlog_lifetime');
        if(!is_null($lt) && !is_bool($lt) && !is_numeric($lt))
            throw new ConfigBadParameterException('statlog_lifetime');
        
        if(is_null($lt) || (is_bool($lt) && !$lt)) {
            // statlog disabled, clean all in case something remains (parameter just changed)
            Logger::info('Statlog disabled, wipe everything out');
            DBI::exec('DELETE FROM '.self::getDBTable());
            return;
        }
        
        if((is_bool($lt) && $lt) || (is_numeric($lt) && !(int)$lt)) { // true or 0 => infinite stats keeping
            Logger::info('Statlog keeping set to infinite, nothing to clean');
            return;
        }
        
        Logger::info('Removing statlogs older than '.(int)$lt.' days');
        $s = DBI::prepare('DELETE FROM '.self::getDBTable().' WHERE created < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $s->execute(array(':days' => (int)$lt));
    }
}
