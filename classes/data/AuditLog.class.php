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
class AuditLog extends DBObject {
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
            'size' => 32
        ),
        'target_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'target_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'author_type' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'author_id' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'ip' => array(
            'type' => 'string',
            'size' => 39,
        ),
        'created' => array(
            'type' => 'datetime'
        )
    );

    protected static $secondaryIndexMap = array(
        'Type_ID' => array( 
            'target_type' => array(),
            'target_id'   => array()
        ),
        'Author_ID' => array( 
            'author_type' => array(),
            'author_id'   => array()
        )
        
//        'Type_ID_AType_AID_IP_Event_Created' => array(
//            'target_type' => array(),
//            'target_id'   => array(),
//            'author_type' => array(),
//            'author_id'   => array(),
//            'ip'          => array(),
//            'event'       => array(),
//            'created'     => array()
//        ),
        
    );

    /**
     * Set selectors
     */
    const FROM_TARGET = 'target_type = :type AND target_id = :id ORDER BY created ASC, id ASC';
    const FROM_AUTHOR = 'author_type = :type AND author_id = :id ORDER BY created ASC, id ASC';
    
    /**
     * Properties
     */
    protected $id = null;
    protected $event = null;
    protected $target_type = null;
    protected $target_id = null;
    protected $author_type = null;
    protected $author_id = null;
    protected $created = null;
    protected $ip = null;
    
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the auditlog from (if already fetched from database)
     * 
     * @throws AuditLogNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new AuditLogNotFoundException('id = '.$id);
        }

        // Fill properties from provided data
        if($data) $this->fillFromDBData($data);
        
    }
    
    /**
     * Create a new audit log
     * 
     * @param LogEventTypes $event the event to be logged
     * @param DBObject the target to be logged
     * @param DBObject the author of the action
     * 
     * @return AuditLog auditlog
     */
    public static function create($event, DBObject $target, $author = null) {
        if(is_null(Config::get('auditlog_lifetime'))) // Auditlog disabled
            return;
        
        // Check event type
        if(!LogEventTypes::isValidValue($event))
            throw new AuditLogUnknownEventException($event);
        
        $auditLog = new self();
        
        $auditLog->event = $event;
        $auditLog->created = time();
        $auditLog->ip = Utilities::getClientIP();
        $auditLog->target_id = $target->id;
        $auditLog->target_type = get_class($target);
        
        if(!$author) $author = Auth::user();
        
        if(is_object($author)) {
            $auditLog->author_type = get_class($author);
            $auditLog->author_id = $author->id;
        }
        
        $auditLog->save();
        
        return $auditLog;
    }
    
    /**
     * Save in database
     */
    public function save() {
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
    public function __get($property) {
        if(in_array($property, array(
            'id', 
            'event',
            'target_type',
            'target_id',
            'author_type',
            'author_id',
            'created',
            'ip', 
        ))) return $this->$property;
        
        if($property == 'target') {
            try {
                return call_user_func($this->target_type.'::fromId', $this->target_id);
            } catch(Exception $e) {
                return (object)array(
                    'made_available_time' => 1,
                    'upload_time' => 1,
                    'name' => 'unknown',
                    'size' => 1,
                    'email' => 'unknown'
                );
            }
        }
        
        if($property == 'author') {
            if(!$this->author_type || !$this->author_id) return null;
            
            try {
                return call_user_func($this->author_type.'::fromId', $this->author_id);
            } catch(Exception $e) {
                return (object)array(
                    'identity' => 'unknown',
                    'email' => 'unknown'
                );
            }
        }
        
        if($property == 'time_taken') {
            if($this->target_type == 'Transfer') {
                if($this->event == LogEventTypes::TRANSFER_AVAILABLE) return $this->target->made_available_time;
                if($this->event == LogEventTypes::UPLOAD_ENDED) return $this->target->upload_time;
            }
            
            if($this->target_type == 'File') {
                if($this->event == LogEventTypes::FILE_UPLOADED) return $this->target->upload_time;
            }
            
            return 0;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Get logs related to a target
     * 
     * @param DBObject $transfer
     * 
     * @return array of AuditLog
     */
    public static function fromTarget(DBObject $target, $event = null) {
        $logs = self::all(self::FROM_TARGET, array('type' => $target->getClassName(), 'id' => (string)$target->id));
        
        if($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function($log) use($event) {
                return $log->event == $event;
            });
        }
        
        return $logs;
    }
    
    /**
     * Get logs related to an author
     * 
     * @param DBObject $author
     * 
     * @return array of AuditLog
     */
    public static function fromAuthor(DBObject $author, $event = null) {
        $logs = self::all(self::FROM_AUTHOR, array('type' => $author->getClassName(), 'id' => (string)$author->id));
        
        if($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function($log) use($event) {
                return $log->event == $event;
            });
        }
        
        return $logs;
    }
    
    /**
     * Get logs related to a transfer
     * 
     * @param Transfer $transfer
     * 
     * @return array of AuditLog
     */
    public static function fromTransfer(Transfer $transfer, $event = null) {
        if(
            !is_object($transfer)
            || !$transfer->id
        ) throw new TransferNotFoundException($transfer->id);
        
        // Get and delete all audit logs related to the transfer
        $logs = array_values(self::all(self::FROM_TARGET, array('type' => $transfer->getClassName(), 'id' => (string)$transfer->id)));
        
        // Add events related to the transfer's files
        foreach(self::all('target_type=\'File\' AND target_id IN :ids', array(':ids' => array_map(function($file) {
            return $file->id;
        }, $transfer->files))) as $log) $logs[] = $log;
        
        // Add events related to the transfer's recipients
        foreach(self::all('target_type=\'Recipient\' AND target_id IN :ids', array(':ids' => array_map(function($recipient) {
            return $recipient->id;
        }, $transfer->recipients))) as $log) $logs[] = $log;
         
        
        // Sort by event date
        usort($logs, function($a, $b) {
            $d = $a->created - $b->created;
            if($d != 0) return $d;
            return $a->id - $b->id;
        });
        
        // filter by type if required
        if($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function($log) use($event) {
                return $log->event == $event;
            });
        }
        
        return $logs;
    }
    
    /**
     * Tells wether the client already downloaded the file set over a past range
     * 
     * @param Recipient $recipient
     * @param array $files_ids
     * @param int $range in seconds
     * 
     * @return bool
     */
    public static function clientRecentlyDownloaded($recipient, $files_ids, $range = 3600) {
        // Get a single download for each file if it exists over the range
        $downloaded = self::all(
            'target_type=\'File\' AND target_id IN :ids AND author_type=\'Recipient\' AND author_id=:rcptid AND ip = :ip AND event=\'download_ended\' AND created > :since GROUP BY target_id, id',
            array(
                ':ids' => $files_ids,
                ':rcptid' => $recipient->id,
                ':ip' => Utilities::getClientIP(),
                ':since' => date('Y-m-d H:i:s', time() - $range)
            )
        );
        
        // Same number of items means all files where downloaded (individually or not) over the range
        return count($downloaded) == count($files_ids);
    }
    
    /**
     * Remove entries related to a transfer
     * 
     * @param Transfer $transfer
     */
    public static function clean(Transfer $transfer) {
        foreach(self::fromTransfer($transfer) as $log)
            $log->delete();
    }
}
