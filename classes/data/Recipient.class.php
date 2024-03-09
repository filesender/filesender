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
 * Represents a recipient in database
 *
 * @property array $transfer related transfer
 */
class Recipient extends DBObject
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
        'transfer_id' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'email' => array(
            'type' => 'string',
            'size' => 255
        ),
        'token' => array(
            'type' => 'string',
            'size' => 60
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'last_activity' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'reminder_count' => array(
            'type' => 'uint',
            'size' => 'medium',
            'default' => 0
        ),
        'last_reminder' => array(
            'type' => 'datetime',
            'null' => true
        )
    );

    protected static $secondaryIndexMap = array(
        'token' => array(
            'token' => array()
        )
    );
    

    /**
     * Properties
     */
    protected $id = null;
    protected $transfer_id = null;
    protected $email = '';
    protected $token = '';
    protected $created = 0;
    protected $last_activity = null;
    protected $options = null;
    protected $reminder_count = 0;
    protected $last_reminder = 0;
    
    /**
     * Related objects cache
     */
    private $transferCache = null;
    private $logsCache = null;
    private $trackingEventsCache = null;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of recipient to load from database (null if loading not wanted)
     * @param array $data data to create the recipient from (if already fetched from database)
     *
     * @throws RecipientNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new RecipientNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Loads recipient from token
     *
     * @param string $token the token
     *
     * @throws RecipientNotFoundException
     *
     * @return Recipient
     */
    public static function fromToken($token)
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE token = :token');
        $statement->execute(array(':token' => $token));
        $data = $statement->fetch();
        if (!$data) {
            throw new RecipientNotFoundException('token = '.$token);
        }
        
        $recipient = self::fromData($data['id'], $data);
        
        return $recipient;
    }

    
    /**
     * Create a new recipient bound to a transfer
     *
     * @param Transfer $transfer the relater transfer
     * @param string $email the recipient email
     *
     * @return Recipient
     */
    public static function create(Transfer $transfer, $email)
    {
        $recipient = new self();
        
        // Init caches to empty to avoid db queries
        $recipient->logsCache = array();
        $recipient->trackingEventsCache = array();
        
        $recipient->transfer_id = $transfer->id;
        $recipient->transferCache = $transfer;
        
        if ($email && !Utilities::validateEmail($email)) {
            throw new BadEmailException($email);
        }
        $recipient->email = $email;
        
        $recipient->created = time();
        
        // Generate token until it is indeed unique
        $recipient->token = Utilities::generateUID(false, function ($token, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.Recipient::getDBTable().' WHERE token = :token');
            $statement->execute(array(':token' => $token));
            $data = $statement->fetch();
            if (!$data) {
                Logger::info('Recipient uid generation took '.$tries.' tries');
            }
            return !$data;
        });
        
        return $recipient;
    }
    
    /**
     * Get recipients from Transfer
     *
     * @param Transfer $transfer the relater transfer
     *
     * @return array of Recipient
     */
    public static function fromTransfer(Transfer $transfer)
    {
        $s = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE transfer_id = :transfer_id');
        $s->execute(array('transfer_id' => $transfer->id));
        $recipients = array();
        foreach ($s->fetchAll() as $data) {
            $recipients[$data['id']] = self::fromData($data['id'], $data);
        } // Don't query twice, use loaded data
        return $recipients;
    }
    
    /**
     * Record activity
     */
    public function recordActivity()
    {
        $this->last_activity = time();
        $this->save();
    }
    
    /**
     * Send reminder
     */
    public function remind()
    {
    
        // Limit reminders
        if ($this->reminder_count >= Config::get('recipient_reminder_limit')) {
            throw new GuestReminderLimitReachedException();
        }
        $this->reminder_count++;
        $this->save();

        $this->transfer->remind($this);
    }
    
    /**
     * Delete the recipient related objects
     */
    public function beforeDelete()
    {
        foreach (TrackingEvent::fromRecipient($this) as $tracking_event) {
            $tracking_event->delete();
        }
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
        if (in_array($property, array('id', 'transfer_id', 'email', 'token', 'created', 'last_activity', 'options'))) {
            return $this->$property;
        }
        
        if ($property == 'transfer') {
            if (is_null($this->transferCache)) {
                $this->transferCache = Transfer::fromId($this->transfer_id);
            }
            return $this->transferCache;
        }
        
        if ($property == 'owner') {
            return $this->transfer->owner;
        }
        
        if ($property == 'auditlogs') {
            if (is_null($this->logsCache)) {
                $this->logsCache = AuditLog::fromAuthor($this);
            }
            return $this->logsCache;
        }
        
        if ($property == 'download_link') {
            return Utilities::http_build_query(
                array( 's'     => 'download',
                       'token' => $this->token )
            );
        }
        
        if ($property == 'downloads') {
            return array_filter($this->auditlogs, function ($log) {
                return $log->event == LogEventTypes::DOWNLOAD_ENDED;
            });
        }
        
        if ($property == 'tracking_events') {
            if (is_null($this->trackingEventsCache)) {
                $this->trackingEventsCache = TrackingEvent::fromRecipient($this);
            }
            return $this->trackingEventsCache;
        }
        
        if ($property == 'errors') {
            return array_filter($this->tracking_events, function ($tracking_event) {
                return in_array($tracking_event->type, array(TrackingEventTypes::BOUNCE));
            });
        }
        
        if ($property == 'identity') {
            return $this->email ? $this->email : (string)Lang::tr('anonymous');
        }
        
        if ($property == 'name') {
            $identity = $this->email ? explode('@', $this->email) : array(Lang::tr('anonymous'));
            return $identity[0];
        }
        
        throw new PropertyAccessException($this, $property);
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
        if ($property == 'options') {
            $this->options = $value;
        } elseif ($property == 'auditlogs') {
            $this->logsCache = (array)$value;
        } elseif ($property == 'trackingevents') {
            $this->trackingEventsCache = (array)$value;
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
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.($this->email ? $this->email : 'anonymous').')';
    }
}
