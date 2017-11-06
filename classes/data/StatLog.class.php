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
            'size' => 'big',
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
            'null' => true
        ),
        'time_taken' => array(
            'type' => 'int',
            'size' => 'big',
            'null' => true
        ),
        'additional_attributes' => array(
            'type' => 'text',
            'transform' => 'json',
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime'
        )
    );

    protected static $secondaryIndexMap = array(
        'created' => array( 
            'created' => array()
        ),
        'event_tt' => array( 
            'event' => array(),
            'target_type' => array()
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
    protected $time_taken = 0;
    protected $additional_attributes = null;
    
    
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
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new StatLogNotFoundException('id = '.$id);
        }

        // Fill properties from provided data
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
        // Check if statlog is enabled
        $lt = Config::get('statlog_lifetime');
        if(is_null($lt) || (is_bool($lt) && !$lt)) return; // statlog disabled
        
        // Check type
        if(!LogEventTypes::isValidValue($event))
            throw new StatLogUnknownEventException($event);
        
        // Create entry
        $log = new self();
        $log->event = $event;
        $log->created = time();
        $log->target_type = get_class($target);
        
        // Add metadata depending on target
        switch ($log->target_type){
            case File::getClassName():
                $log->size = $target->size;
                
                if($event == LogEventTypes::FILE_UPLOADED) {
                    $log->time_taken = $target->upload_time;
		    $log->additional_attributes = array('encryption'=>$target->transfer->options['encryption']);
		}
                break;
            
            case Transfer::getClassName():
                $log->size = $target->size;
                
                if($event == LogEventTypes::UPLOAD_ENDED)
                    $log->time_taken = $target->upload_time;
                
                if($event == LogEventTypes::TRANSFER_AVAILABLE)
                    $log->time_taken = $target->made_available_time;
                break;
            
            default:
                $log->size = 0;
                break;
        }
        
        // Add user aditionnal attributes if enabled
        if(Config::get('statlog_log_user_additional_attributes')) {
            $additional_attributes = null;
            
            if(Auth::isAuthenticated()) {
                if(Auth::isSP())
                    $additional_attributes = Auth::user()->additional_attributes;
                
                if(Auth::isGuest())
                    $additional_attributes = AuthGuest::getGuest()->owner->additional_attributes;
            }
            
            if($log->target_type == 'File')
                $additional_attributes = $target->transfer->owner->additional_attributes;
            
            if($log->target_type == 'Transfer')
                $additional_attributes = $target->owner->additional_attributes;
            
            $additional_attributes = (array)$additional_attributes;
            
            $attrs = Config::get('auth_sp_additional_attributes');
            if(!$attrs || !array_key_exists('name', $attrs))
                if(array_key_exists('name', $additional_attributes))
                    unset($additional_attributes['name']);
            
            if(count($additional_attributes))
                $log->additional_attributes = $additional_attributes;
        }
        
        $log->save();
        
        return $log;
    }
    
    /**
     * Create a new global stat log
     * 
     * @param StatEvent $event the event to be logged
     * @param integer $size
     * 
     * @return StatLog
     */
    public static function createGlobal($event, $size = 0) {
        // Check if statlog is enabled
        $lt = Config::get('statlog_lifetime');
        if(is_null($lt) || (is_bool($lt) && !$lt)) return; // statlog disabled
        
        // Check type
        if(!LogEventTypes::isValidValue($event) || !preg_match('`^global_`', $event))
            throw new StatLogUnknownEventException($event);
        
        // Create entry
        $log = new self();
        $log->event = $event;
        $log->created = time();
        $log->target_type = 'global';
        $log->size = $size;
        
        $log->save();
        
        return $log;
    }
    
    /**
     * Count events of a given type over a period of time
     * 
     * @param string $event
     * @param int $start timestamp
     * @param int $end timestamp
     * 
     * @return array of info
     */
    public static function getEventCount($event, $start = null, $end = null) {
        // Check if statlog is enabled
        $lt = Config::get('statlog_lifetime');
        if(is_null($lt) || (is_bool($lt) && !$lt)) return null; // Disabled
        
        // Build query depending on time range
        $query = 'SELECT COUNT(*) AS cnt, MIN(created) AS start, MAX(created) AS end FROM '.self::getDBTable().' WHERE event = :event';
        if(!is_null($start)) $query .= ' AND created >= "'.date('Y-m-d H:i:s', $start).'"';
        if(!is_null($end)) $query .= ' AND created <= "'.date('Y-m-d H:i:s', $end).'"';
        
        // Run the search
        $statement = DBI::prepare($query);
        $statement->execute(array(':event' => $event));
        $data = $statement->fetch();
        
        return array(
            'count' => $data['cnt'],
            'start' => strtotime($data['start']),
            'end' => strtotime($data['end'])
        );
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
            'time_taken',
            'additional_attributes',
        ))) return $this->$property;
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Clean logs
     */
    public static function clean() {
        // Check if statlog is enabled
        $lt = Config::get('statlog_lifetime');
        if(!is_null($lt) && !is_bool($lt) && !is_numeric($lt))
            throw new ConfigBadParameterException('statlog_lifetime');
        
        Logger::info('Cleaning statlogs');
        
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
