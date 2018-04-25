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
if(!defined('FILESENDER_BASE')) die('Missing environment');

/**
 * Represents an user in database
 */
class Guest extends DBObject {
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
        'user_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'user_email' => array(
            'type' => 'string',
            'size' => 250
        ),
        'token' => array(
            'type' => 'string',
            'size' => 60,
            'unique' => true
        ),
        'email' => array(
            'type' => 'string',
            'size' => 255
        ),
        'transfer_count' => array(
            'type' => 'uint',
            'size' => 'medium'
        ),
        'subject' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'message' => array(
            'type' => 'text',
            'null' => true
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'transfer_options' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'status' => array(
            'type' => 'string',
            'size' => 32
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'expires' => array(
            'type' => 'datetime'
        ),
        'last_activity' => array(
            'type' => 'datetime'
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
    
    /**
     * Set selectors
     */
    const AVAILABLE = "status = 'available' ORDER BY created DESC";
    const EXPIRED = "expires < :date ORDER BY expires ASC";
    const FROM_USER = "user_id = :user_id AND expires > :date ORDER BY created DESC";
    const FROM_USER_AVAILABLE = "user_id = :user_id AND expires > :date AND status = 'available' ORDER BY created DESC";
    
    /**
     * Properties
     */
    protected $id = null;
    protected $user_id = null;
    protected $user_email = null;
    protected $token = null;
    protected $email = null;
    protected $transfer_count = 0;
    protected $subject = null;
    protected $message = null;
    protected $options = null;
    protected $transfer_options = null;
    protected $status = null;
    protected $created = 0;
    protected $expires = 0;
    protected $last_activity = 0;
    protected $reminder_count = 0;
    protected $last_reminder = 0;

    /**
     * Cache
     */
    private $transfersCache = null;
    private $trackingEventsCache = null;
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of guest to load from database (null if loading not wanted)
     * @param array $data data to create the guest from (if already fetched from database)
     * 
     * @throws GuestNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new GuestNotFoundException('id = '.$id);
        }
        
        // Fill properties from provided data
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Related to legacy options support
     */
    protected function fillFromDBData($data, $transforms = array()) {
        parent::fillFromDBData($data, $transforms);
        
        // Legacy option format conversion, will be transformed to object by json conversion
        if(is_array($this->options)) $this->options = array_merge(
            array_fill_keys(array_keys(self::allOptions()), false),
            array_fill_keys($this->options, true)
        );
        
        if(is_object($this->options)) $this->options = (array)$this->options;
        
        // Legacy option format conversion, will be transformed to object by json conversion
        if(is_array($this->transfer_options)) {
            $this->transfer_options = array_merge(
                array_fill_keys(array_keys(Transfer::allOptions()), false),
                array_fill_keys($this->transfer_options, true)
            );
        }
        if(is_object($this->transfer_options))
            $this->transfer_options = (array)$this->transfer_options;
    }
    
    /**
     * Create a new guest
     * 
     * @param integer $recipient recipient email, mandatory
     * @param integer $from sender email
     * 
     * @return Guest
     */
    public static function create($recipient, $from = null) {
        $guest = new self();
        $time = time();
        
        // Init cache to empty to avoid db queries
        $guest->trackingEventsCache = array();
        
        if(!$from) $from = Auth::user()->email;
        
        // If not remote user from address must be one of the user addresses
        if(!Auth::isRemote()) {
            if(!in_array($from, Auth::user()->email_addresses))
                throw new BadEmailException($from);
        }
        
        $guest->user_id = Auth::user()->id;
        $guest->__set('user_email', $from);
        $guest->__set('email', $recipient); // Throws
        
        $guest->status = GuestStatuses::CREATED;
        $guest->created = $time;
        
        // Generate token until it is indeed unique
        $guest->token = Utilities::generateUID(function($token, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.Guest::getDBTable().' WHERE token = :token');
            $statement->execute(array(':token' => $token));
            $data = $statement->fetch();
            if(!$data) Logger::info('Guest uid generation took '.$tries.' tries');
            return !$data;
        });
        
        return $guest;
    }
    
    /**
     * Get default expire date
     * 
     * @return int timestamp
     */
    public static function getDefaultExpire() {
        $days = Config::get('default_guest_days_valid');
        if(!$days) $days = Config::get('default_transfer_days_valid');
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get max expire date
     * 
     * @return int timestamp
     */
    public static function getMaxExpire() {
        $days = Config::get('max_guest_days_valid');
        if(!$days) $days = Config::get('max_transfer_days_valid');
        
        if(!$days) $days = Config::get('default_daysvalid'); // @deprecated legacy
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get available guests
     * 
     * @return array of Guest
     */
    public static function allAvailable() {
        return self::all(self::AVAILABLE);
    }
    
    /**
     * Get expired guests
     * 
     * @return array of Guest
     */
    public static function allExpired() {
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d')));
    }
    
    /**
     * Get guests from user
     * 
     * @param mixed $user User or user id
     * 
     * @return array of Guests
     */
    public static function fromUser($user) {
        if($user instanceof User) $user = $user->id;
        
        return self::all(self::FROM_USER, array(':user_id' => $user, ':date' => date('Y-m-d')));
    }

    /**
     * Get available guests from user
     * 
     * @param mixed $user User or user id
     * 
     * @return array of Guests
     */
    public static function fromUserAvailable($user) {
        if($user instanceof User) $user = $user->id;
        
        return self::all(self::FROM_USER_AVAILABLE, array(':user_id' => $user, ':date' => date('Y-m-d')));
    }
    
    /**
     * Loads guest from token
     * 
     * @param string $token the token
     * 
     * @throws RecipientNotFoundException
     * 
     * @return Guest
     */
    public static function fromToken($token) {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE token = :token');
        $statement->execute(array(':token' => $token));
        $data = $statement->fetch();
        if(!$data) throw new GuestNotFoundException('token = '.$token);
        
        $guest = self::fromData($data['id'], $data);
        
        return $guest;
    }
    
    /**
     * Tells wether the guest is expired
     * 
     * @return bool
     */
    public function isExpired() {
        $today = (24 * 3600) * floor(time() / (24 * 3600));
        return $this->expires < $today;
    }
    
    /**
     * Check if user owns current gueest
     * 
     * @param miwed $user User or user id to compare with
     * 
     * @return bool
     */
    public function isOwner($user) {
        return $this->owner->is($user);
    }
    
    /**
     * Set guest as available, sends notifications
     */
    public function makeAvailable() {
        $this->status = GuestStatuses::AVAILABLE;
        $this->save();
        
        // Update sender's frequent recipient list
        Auth::user()->saveFrequentRecipients(array($this->email));
        
        // Save choosen guest options in user preferences
        $this->owner->saveGuestOptions($this->options);
        
        // Log to audit/stat
        Logger::logActivity(LogEventTypes::GUEST_CREATED, $this);
        
        // Send notification to recipient
        if($this->getOption(GuestOptions::EMAIL_GUEST_CREATED))
            TranslatableEmail::quickSend('guest_created', $this);
        
        // Send receipt to owner
        if($this->getOption(GuestOptions::EMAIL_GUEST_CREATED_RECEIPT))
            TranslatableEmail::quickSend('guest_created_receipt', $this->owner, $this);
        
        Logger::info($this.' created');
    }
    
    /**
     * Send reminder to recipients
     */
    public function remind() {
        
        // Limit reminders
        if( $this->reminder_count >= Config::get('guest_reminder_limit')) {
            throw new GuestReminderLimitReachedException();
        }
        $this->reminder_count++;
        $this->save();
        
        TranslatableEmail::quickSend('guest_reminder', $this);
            
        Logger::info($this.' reminded');
    }
    
    /**
     * Close the guest
     * 
     * @param bool $manually wether the guest was closed on request (if not it means it expired)
     */
    public function close($manualy = true) {
        // Close the guest
        $this->status = GuestStatuses::CLOSED;
        $this->save();
        
        // Log to audit/stat
        Logger::logActivity(
            $manualy ? LogEventTypes::GUEST_CLOSED : LogEventTypes::GUEST_EXPIRED,
            $this
        );
        
        // Sending notification to recipient
        if($this->getOption(GuestOptions::EMAIL_GUEST_EXPIRED))
            TranslatableEmail::quickSend($manualy ? 'guest_cancelled' : 'guest_expired', $this);
        
        Logger::info($this.' '.($manualy ? 'removed' : 'expired'));
    }
    
    
    /**
     * Get all options
     * 
     * @return array
     */
    public static function allOptions() {
        // Get defaults
        $options = Config::get('guest_options');
        if(!is_array($options)) $options = array();

        self::validateOptions($options);
        return $options;
    }

    /**
     * Perform reasonably fast validation of config options.
     * This allows the global config loader to check with many classes
     * by calling class::validateConfig() so that particular pages do not
     * have to be loaded to find configuration issues.
     */
    public static function validateConfig() {
        self::allOptions();
    }
    
    /**
     * Get user available options
     * 
     * @param bool $advanced if not null filter by advanced status as well
     * 
     * @return array
     */
    public static function availableOptions($advanced = null) {
        return array_filter(self::allOptions(), function($o) use($advanced) {
            if(!$o['available']) return false;
            
            if(!is_null($advanced))
                return $o['advanced'] == $advanced;
            
            return true;
        });
    }
    
    /**
     * Get option value
     * 
     * @param string $option
     * 
     * @return mixed
     */
    public function getOption($option) {
        if(array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        $options = static::allOptions();
        if(array_key_exists($option, $options)) {
            if(array_key_exists('default', $options[$option])) {
                return $options[$option]['default'];
            }
        }
        return false;
    }
    
    /**
     * Delete the guest related objects
     */
    public function beforeDelete() {
        foreach(TrackingEvent::fromGuest($this) as $tracking_event) $tracking_event->delete();
        
        foreach(TranslatableEmail::fromContext($this) as $translatable_email) $translatable_email->delete();
    }
    
    /**
     * Validate and format options.
     * throws an exception if the raw_options contain invalid data
     * 
     * @param mixed $raw_options
     * 
     * @return array
     */
    public static function validateOptions($raw_options) {
        $options = array();
        foreach((array)$raw_options as $name => $value) {
            if(!GuestOptions::isValidValue($name)) {
                throw new BadOptionNameException($name,
                   'Please check if you have an invalid key in your guest_options configuration. '
                   . GuestOptions::getConfigKeysAsLogString()
                );
            }
            $value = (bool)$value;
            
            $options[$name] = $value;
        }
        
        return $options;
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
            'id', 'user_id', 'user_email', 'token', 'email', 'transfer_count',
            'subject', 'message', 'options', 'transfer_options', 'status', 'created', 'expires', 'last_activity'
        ))) return $this->$property;
        
        if($property == 'user' || $property == 'owner') {
            $user = User::fromId($this->user_id);
            $user->email_addresses = $this->user_email;
            return $user;
        }
        
        if($property == 'upload_link') {
            return Config::get('site_url').'?s=upload&vid='.$this->token;
        }
        
        if($property == 'transfers') {
            if(is_null($this->transfersCache)) $this->transfersCache = Transfer::fromGuest($this);
            return $this->transfersCache;
        }
        
        if($property == 'tracking_events') {
            if(is_null($this->trackingEventsCache)) $this->trackingEventsCache = TrackingEvent::fromGuest($this);
            return $this->trackingEventsCache;
        }
        
        if($property == 'errors') {
            return array_filter($this->tracking_events, function($tracking_event) {
                return in_array($tracking_event->type, array(TrackingEventTypes::BOUNCE));
            });
        }
        
        if($property == 'identity') {
            return $this->email;
        }
        
        if($property == 'name') {
            $identity = explode('@', $this->email);
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
     * @throws GuestBadStatusException
     * @throws BadExpireException
     * @throws BadEmailException
     * @throws PropertyAccessException
     */
    public function __set($property, $value) {
        if($property == 'status') {
            $value = strtolower($value);
            if(!GuestStatuses::isValidValue($value)) throw new GuestBadStatusException($value);
            $this->status = (string)$value;
            
        }else if($property == 'user_email') {
            if(!Utilities::validateEmail($value)) throw new BadEmailException($value);
            $this->user_email = (string)$value;
            
        }else if($property == 'subject') {
            $this->subject = (string)$value;
            
        }else if($property == 'message') {
            $this->message = (string)$value;
            
        }else if($property == 'options') {
            $this->options = self::validateOptions($value);
            
        }else if($property == 'transfer_options') {
            $this->transfer_options = Transfer::validateOptions($value);
            
        }else if($property == 'transfer_count') {
            $this->transfer_count = (int)$value;
            
        }else if($property == 'email') {
            if(!Utilities::validateEmail($value)) throw new BadEmailException($value);
            $this->email = (string)$value;
            
        }else if($property == 'expires' || $property == 'last_activity') {
            if(preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}$`', $value)) {
                $value = strtotime($value);
            }
            
            if(!preg_match('`^[0-9]+$`', $value)) throw new BadExpireException($value);
            
            $value = (int)$value;
            if($value < floor(time() / (24 * 3600)) || $value > self::getMaxExpire()) {
                throw new BadExpireException($value);
            }
            $this->$property = (string)$value;
            
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * String caster
     * 
     * @return string
     */
    public function __toString() {
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->email.')';
    }
}
