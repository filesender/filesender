<?php
/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 *  Represents a tracking event in the database
 */
class TrackingEvent extends DBObject
{
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
        'type' => array(
            'type' => 'string',
            'size' => 16
        ),
        'target_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'target_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'details' => array(
            'type' => 'text'
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'reported' => array(
            'type' => 'datetime',
            'null' => true
        )
    );

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . DBView::columnDefinition_age($dbtype, 'reported')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    protected static $secondaryIndexMap = array(
        'type_id' => array(
            'target_type' => array(),
            'id'          => array()
        )
    );

    /**
     * Properties
     */
    protected $id = null;
    protected $type = null;
    protected $target_type = null;
    protected $target_id = null;
    protected $details = null;
    protected $created = 0;
    protected $reported = 0;
   
    /**
     * Constructor
     *
     * @param integer $id identifier of tracking event to load from database (null if loading not wanted)
     * @param array $data data to create the tracking event from (if already fetched from database)
     *
     * @throws TrackingEventNotFoundException
     */
    public function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new TrackingEventNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Create a new tracking event (just received)
     *
     * @param string $type
     * @param DBObject $target
     * @param mixed $date
     * @param string $details
     *
     * @return self
     */
    public static function create($type, DBObject $target, $date, $details)
    {
        // Check given type
        if (!TrackingEventTypes::isValidValue($type)) {
            throw new TrackingEventUnknownEventException($type);
        }
        
        $tracking = new self();
        
        $tracking->type = $type;
        $tracking->target_type = get_class($target);
        $tracking->target_id = $target->id;
        
        $tracking->created = (int)$date ? (int)$date : time();
        
        $tracking->details = $details;
        
        return $tracking;
    }
    
    /**
     * Report tracking event to content author
     */
    public function report()
    {
        self::reportSet(array($this));
    }
    
    /**
     * Report tracking event set to content author
     *
     * @param array $tracking_events
     */
    public static function reportSet($tracking_events)
    {
        // Group events by type (bounce ...)
        $by_type = array();
        foreach ($tracking_events as $tracking_event) {
            if (!array_key_exists($tracking_event->type, $by_type)) {
                $by_type[$tracking_event->type] = array();
            }
            $by_type[$tracking_event->type][] = $tracking_event;
        }
        
        // Send separate notification for each type
        foreach ($by_type as $type => $set) {
            ApplicationMail::quickSend($type.'_report', $set[0]->target->owner->email, array($type.'s' => $set));
            
            foreach ($set as $tracking_event) {
                $tracking_event->reported = time();
                $tracking_event->save();
            }
        }
    }
    
    /**
     * Get non-reported tracking events of type grouped by target type
     *
     * @param string $type
     *
     * @return array
     */
    public static function getNonReported($type)
    {
        // Check type
        if (!TrackingEventTypes::isValidValue($type)) {
            throw new TrackingEventUnknownEventException($type);
        }
        
        $tracking_events = array();
        
        // Gather and group by target
        foreach (self::all('reported IS NULL AND type = :type ORDER BY created', array(':type' => $type)) as $tracking_event) {
            if ($tracking_event->target_type == 'Recipient') {
                $tid = 'Transfer#'.$tracking_event->target->transfer->id;
            } elseif ($tracking_event->target_type == 'Guest') {
                $tid = $tracking_event->target_type.'#'.$tracking_event->target_id;
            }
            
            if (!array_key_exists($tid, $tracking_events)) {
                $tracking_events[$tid] = array();
            }
            $tracking_events[$tid][] = $tracking_event;
        }
        
        return $tracking_event;
    }
    
    /**
     * Get tracking events from transfer
     *
     * @param Transfer $transfer
     *
     * @return array
     */
    public static function fromTransfer($transfer)
    {
        // Gather transfer's recipients ids
        $ids = array_map(function ($recipient) {
            return $recipient->id;
        }, $transfer->recipients);
        
        // Recipientless transfer cannot have tracking events
        if (!count($ids)) {
            return array();
        }
        
        return self::all('target_type=\'Recipient\' AND target_id IN :ids ORDER BY created', array(':ids' => $ids));
    }
    
    /**
     * Get tracking events from recipient
     *
     * @param Recipient $recipient
     *
     * @return array
     */
    public static function fromRecipient($recipient)
    {
        return self::all('target_type=\'Recipient\' AND target_id = :id ORDER BY created', array(':id' => $recipient->id));
    }
    
    /**
     * Get tracking events from guest
     *
     * @param Guest $guest
     *
     * @return array
     */
    public static function fromGuest($guest)
    {
        return self::all('target_type=\'Guest\' AND target_id = :id ORDER BY created', array(':id' => $guest->id));
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
            'id', 'type', 'target_type', 'target_id', 'details', 'created', 'reported'
        ))) {
            return $this->$property;
        }
        
        if ($property == 'date') {
            return $this->created;
        }
        
        if ($property == 'target') {
            return call_user_func($this->target_type.'::fromId', $this->target_id);
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     */
    public function __set($property, $value)
    {
        throw new PropertyAccessException($this, $property);
    }

    /**
     * Clean old entries
     */
    public static function clean()
    {
        $days = Config::get('trackingevents_lifetime');
        
        /** @var PDOStatement $statement */
        $statement = DBI::prepare('DELETE FROM '.self::getDBTable().' WHERE created < :date');
        $statement->execute(array(':date' => date('Y-m-d', time() - $days * 86400)));
    }
}
