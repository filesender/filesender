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
 * Represents an user in database
 */
class Guest extends DBObject
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
            'size' => 'big'
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
            'type' => 'datetime',
            'null' => true
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
        ),
        'expiry_extensions' => array(
            'type' => 'uint',
            'size' => 'small',
            'default' => 0
        ),

        // Shared guest/user Principal options
        'service_aup_accepted_version' => array(
            'type' => 'uint',
            'size' => 'medium',
            'null' => false,
            'default' => 0
        ),
        'service_aup_accepted_time' => array(
            'type' => 'datetime',
            'null' => true
        ),
    );

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . DBView::columnDefinition_age($dbtype, 'expires')
                        . DBView::columnDefinition_age($dbtype, 'last_activity', 'last_activity_days_ago')
                        . DBView::columnDefinition_age($dbtype, 'last_reminder', 'last_reminder_days_ago')
                        . DBView::columnDefinition_age($dbtype, 'service_aup_accepted_time', 'service_aup_accepted_time_days_ago')
                        . ' , expires < now() as expired '
                        . " , status = 'available' as is_available "
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }

    /**
     * Config variables
     */
    const OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY = "allow_guest_expiry_date_extension";

    /**
     * Set selectors
     */
    const AVAILABLE = "status = 'available' ORDER BY created DESC";
    // Note that if the guest does not expire then expires is null 
    // so that tuple will not be returned by the below fragment.
    const EXPIRED = "expires < :date ORDER BY expires ASC";
    // For these fragments we want to find the guests that have
    // expires is null because they are still considered active
    const FROM_USER = "userid = :userid AND (expires is null or expires > :date) ORDER BY created DESC";
    const FROM_USER_AVAILABLE = "userid = :userid AND (expires is null or expires > :date) AND status = 'available' ORDER BY created DESC";
    
    /**
     * Properties
     */
    protected $id = null;
    protected $user_email = null;
    protected $userid = null;
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
    protected $expiry_extensions = 0;
    protected $service_aup_accepted_version = 0;
    protected $service_aup_accepted_time = null;

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
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new GuestNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Related to legacy options support
     */
    protected function fillFromDBData($data, $transforms = array())
    {
        parent::fillFromDBData($data, $transforms);
        
        // Legacy option format conversion, will be transformed to object by json conversion
        if (is_array($this->options)) {
            $this->options = array_merge(
            array_fill_keys(array_keys(self::allOptions()), false),
            array_fill_keys($this->options, true)
        );
        }
        
        if (is_object($this->options)) {
            $this->options = (array)$this->options;
        }
        
        // Legacy option format conversion, will be transformed to object by json conversion
        if (is_array($this->transfer_options)) {
            $this->transfer_options = array_merge(
                array_fill_keys(array_keys(Transfer::allOptions()), false),
                array_fill_keys($this->transfer_options, true)
            );
        }
        if (is_object($this->transfer_options)) {
            $this->transfer_options = (array)$this->transfer_options;
        }
    }
    
    /**
     * Create a new guest
     *
     * @param integer $recipient recipient email, mandatory
     * @param integer $from sender email
     *
     * @return Guest
     */
    public static function create($recipient, $from = null)
    {
        $guest = new self();
        $time = time();
        
        // Init cache to empty to avoid db queries
        $guest->trackingEventsCache = array();
        
        if (!$from) {
            $from = Auth::user()->email;
        }
        
        // If not remote user from address must be one of the user addresses
        if (!Auth::isRemote()) {
            if (!in_array($from, Auth::user()->email_addresses)) {
                throw new BadEmailException($from);
            }
        }
        
        $guest->userid = Auth::user()->id;
        $guest->__set('user_email', $from);
        $guest->__set('email', $recipient); // Throws
        
        $guest->status = GuestStatuses::CREATED;
        $guest->created = $time;
        
        // Generate token until it is indeed unique
        $guest->token = Utilities::generateUID(false, function ($token, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.Guest::getDBTable().' WHERE token = :token');
            $statement->execute(array(':token' => $token));
            $data = $statement->fetch();
            if (!$data) {
                Logger::info('Guest uid generation took '.$tries.' tries');
            }
            return !$data;
        });
        
        return $guest;
    }
    
    /**
     * Get default expire date
     *
     * @return int timestamp
     */
    public function getDefaultExpire()
    {
        $days = $this->owner->guest_expiry_default_days;
        
        if (!$days) {
            $days = Config::get('default_guest_days_valid');
        }
        if (!$days) {
            $days = Config::get('default_transfer_days_valid');
        }
        
        return strtotime('+'.$days.' day');
    }

    /**
     * Get min expire date. If a number of days is explicitly set then it is used
     * otherwise we default to right now being the min value so that calling code
     * can Utilities::clamp() using this min value without needing to check if 
     * min_guest_days_valid is set.
     *
     * @return int timestamp
     */
    public static function getMinExpire()
    {
        $days = Config::get('min_guest_days_valid');
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get max expire date
     *
     * @return int timestamp
     */
    public static function getMaxExpire()
    {
        $days = Config::get('max_guest_days_valid');
        if (!$days) {
            $days = Config::get('max_transfer_days_valid');
        }
        
        if (!$days) {
            $days = Config::get('default_daysvalid');
        } // @deprecated legacy
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get available guests
     *
     * @return array of Guest
     */
    public static function allAvailable()
    {
        return self::all(self::AVAILABLE);
    }
    
    /**
     * Get expired guests
     *
     * @return array of Guest
     */
    public static function allExpired()
    {
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d')));
    }
    
    /**
     * Get guests from user
     *
     * @param mixed $user User or user id
     *
     * @return array of Guests
     */
    public static function fromUser($user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        
        return self::all(self::FROM_USER, array(':userid' => $user, ':date' => date('Y-m-d')));
    }

    /**
     * Get available guests from user
     *
     * @param mixed $user User or user id
     *
     * @return array of Guests
     */
    public static function fromUserAvailable($user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        
        return self::all(self::FROM_USER_AVAILABLE, array(':userid' => $user, ':date' => date('Y-m-d')));
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
    public static function fromToken($token)
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE token = :token');
        $statement->execute(array(':token' => $token));
        $data = $statement->fetch();
        if (!$data) {
            throw new GuestNotFoundException('token = '.$token);
        }
        
        $guest = self::fromData($data['id'], $data);
        
        return $guest;
    }
    
    /**
     * Tells wether the guest is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        if( is_null($this->expires)) {
            return false;
        }
        $today = (24 * 3600) * floor(time() / (24 * 3600));
        return $this->expires < $today;
    }

    /**
     * Tells wether the guest has expired before a given number of days
     * from now
     *
     * @return bool
     */
    public function isExpiredDaysAgo($days)
    {
        if( is_null($this->expires)) {
            return false;
        }
        $daysSinceEpoch = floor(time() / (24 * 3600)); // days to now()
        $chosenExpireTime  = $daysSinceEpoch - $days;  // move that back selected $days
        $chosenExpireTime *= (24 * 3600);              // convert to time_t
        return $this->expires < $chosenExpireTime;     // compare
    }
    
    /**
     * Check if user owns current gueest
     *
     * @param miwed $user User or user id to compare with
     *
     * @return bool
     */
    public function isOwner($user)
    {
        return $this->owner->is($user);
    }
    
    /**
     * Set guest as available, sends notifications
     */
    public function makeAvailable()
    {
        $this->status = GuestStatuses::AVAILABLE;
        $this->save();
        
        // Update sender's frequent recipient list
        Auth::user()->saveFrequentRecipients(array($this->email));
        
        // Save choosen guest options in user preferences
        $this->owner->saveGuestOptions($this->options);
        
        // Log to audit/stat
        Logger::logActivity(LogEventTypes::GUEST_CREATED, $this);
        
        // Send notification to recipient
        if ($this->getOption(GuestOptions::EMAIL_GUEST_CREATED)) {
            TranslatableEmail::quickSend('guest_created', $this);
        }
        
        // Send receipt to owner
        if ($this->getOption(GuestOptions::EMAIL_GUEST_CREATED_RECEIPT)) {
            TranslatableEmail::quickSend('guest_created_receipt', $this->owner, $this);
        }
        
        Logger::info($this.' created');
    }
    
    /**
     * Send reminder to recipients
     */
    public function remind()
    {
        
        // Limit reminders
        if ($this->reminder_count >= Config::get('guest_reminder_limit')) {
            throw new GuestReminderLimitReachedException();
        }
        
        // Can only remind $x times per day
        Logger::logActivityRateLimited( 'GuestReminderRateLimitReachedException',
                                        'guest_reminder_limit_per_day',
                                        LogEventTypes::GUEST_REMIND_RATE, $this );
        $this->reminder_count++;
        $this->save();

        
        
        TranslatableEmail::quickSend('guest_reminder', $this);
            
        Logger::info($this.' reminded');
    }

    /**
     * Has the guest already been close()d in the database
     */
    public function isClosed()
    {
        return $this->status == GuestStatuses::CLOSED;
    }
    
    /**
     * Close the guest
     *
     * @param bool $manually wether the guest was closed on request (if not it means it expired)
     */
    public function close($manualy = true)
    {
        // Close the guest
        $this->status = GuestStatuses::CLOSED;
        $this->save();
        
        // Log to audit/stat
        Logger::logActivity(
            $manualy ? LogEventTypes::GUEST_CLOSED : LogEventTypes::GUEST_EXPIRED,
            $this
        );

        //
        // if the user is manually closing a guest then we audit some other
        // properties to detect churn.
        //
        if( $manualy )
        {
            $count = Transfer::countUploadedFromGuest($this);
            if( !$count ) {
                Logger::logActivity( LogEventTypes::GUEST_CLOSED_UNUSED, $this );
            }
        }
        
        // Sending notification to recipient
        if ($this->getOption(GuestOptions::EMAIL_GUEST_EXPIRED)) {
            TranslatableEmail::quickSend($manualy ? 'guest_cancelled' : 'guest_expired', $this);
        }
        
        Logger::info($this.' '.($manualy ? 'removed' : 'expired'));
    }
    
    
    /**
     * Get all options
     *
     * @return array
     */
    public static function allOptions()
    {
        // Get defaults
        $options = Config::get('guest_options');
        if (!is_array($options)) {
            $options = array();
        }
        
        self::validateOptions($options);
        return $options;
    }

    /**
     * Perform reasonably fast validation of config options.
     * This allows the global config loader to check with many classes
     * by calling class::validateConfig() so that particular pages do not
     * have to be loaded to find configuration issues.
     */
    public static function validateConfig( $allowSlowerTests = false )
    {
        self::allOptions();

        if( $allowSlowerTests ) {
        }
        
    }
    
    /**
     * Get user available options
     *
     * @param bool $advanced if not null filter by advanced status as well
     *
     * @return array
     */
    public static function availableOptions($advanced = null)
    {
        return array_filter(self::allOptions(), function ($o) use ($advanced) {
            if (!$o['available']) {
                return false;
            }
            
            if (!is_null($advanced)) {
                return $o['advanced'] == $advanced;
            }
            
            return true;
        });
    }

    /**
     * Get options that are not available for user setting
     *
     * @return array
     */
    public static function forcedOptions()
    {
        return array_filter(self::allOptions(), function ($o) {
            if (!$o['available']) {
                return true;
            }
        });
    }

    
    
    /**
     * Get option value
     *
     * @param string $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        $options = static::allOptions();
        if (array_key_exists($option, $options)) {
            if (array_key_exists('default', $options[$option])) {
                return $options[$option]['default'];
            }
        }
        return false;
    }
    
    /**
     * Delete the guest related objects
     */
    public function beforeDelete()
    {
        foreach (TrackingEvent::fromGuest($this) as $tracking_event) {
            $tracking_event->delete();
        }
        
        foreach (TranslatableEmail::fromContext($this) as $translatable_email) {
            $translatable_email->delete();
        }
    }
    
    /**
     * Validate and format options.
     * throws an exception if the raw_options contain invalid data
     *
     * @param mixed $raw_options
     *
     * @return array
     */
    public static function validateOptions($raw_options)
    {
        $options = array();
        foreach ((array)$raw_options as $name => $value) {
            if (!GuestOptions::isValidValue($name)) {
                throw new BadOptionNameException(
                    $name,
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
    public function __get($property)
    {
        if (in_array($property, array(
            'id', 'user_email', 'token', 'email', 'transfer_count',
            'subject', 'message', 'options', 'transfer_options', 'status', 'created', 'expires', 'last_activity', 'userid'
            , 'expiry_extensions', 'service_aup_accepted_version', 'service_aup_accepted_time'
        ))) {
            return $this->$property;
        }
        if ($property == 'expires_in_days') {
            $v = $this->expires - time();
            if( $v < (24*3600)) {
                $v = 0;
            } else {
                $v = ceil( $v / (24*3600));
            }
            return $v;
        }
        
        if ($property == 'user' || $property == 'owner') {
            $user = User::fromId($this->userid);
            $user->email_addresses = $this->user_email;
            return $user;
        }

        if ($property == 'saml_user_identification_uid') {
            $user = User::fromId($this->userid);
            return $user->saml_user_identification_uid;
        }
        
        
        if ($property == 'upload_link') {
            return Utilities::http_build_query(
                array( 's'   => 'upload',
                       'vid' => $this->token )
            );
        }
        
        if ($property == 'transfers') {
            if (is_null($this->transfersCache)) {
                $this->transfersCache = Transfer::fromGuest($this);
            }
            return $this->transfersCache;
        }
        
        if ($property == 'tracking_events') {
            if (is_null($this->trackingEventsCache)) {
                $this->trackingEventsCache = TrackingEvent::fromGuest($this);
            }
            return $this->trackingEventsCache;
        }
        
        if ($property == 'errors') {
            return array_filter($this->tracking_events, function ($tracking_event) {
                return in_array($tracking_event->type, array(TrackingEventTypes::BOUNCE));
            });
        }
        
        if ($property == 'identity') {
            return $this->email;
        }
        
        if ($property == 'name') {
            $identity = explode('@', $this->email);
            return $identity[0];
        }

        if ($property == 'expiry_date_extension') {
            return $this->getObjectExpiryDateExtension(false);
        } // No throw
        
        //
        // Simple access to $this->options 
        //
        if ($property == 'does_not_expire') {
            return $this->getOption(GuestOptions::DOES_NOT_EXPIRE);
        }
        if ($property == 'email_upload_started') {
            return $this->getOption(GuestOptions::EMAIL_UPLOAD_STARTED);
        }
        if ($property == 'email_upload_page_access') {
            return $this->getOption(GuestOptions::EMAIL_UPLOAD_PAGE_ACCESS);
        }
        if ($property == 'valid_only_one_time') {
            return $this->getOption(GuestOptions::VALID_ONLY_ONE_TIME);
        }
        if ($property == 'can_only_send_to_me') {
            return $this->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME);
        }
        if ($property == 'email_guest_created') {
            return $this->getOption(GuestOptions::EMAIL_GUEST_CREATED);
        }
        if ($property == 'email_guest_created') {
            return $this->getOption(GuestOptions::EMAIL_GUEST_CREATED);
        }
        if ($property == 'email_guest_created_receipt') {
            return $this->getOption(GuestOptions::EMAIL_GUEST_CREATED_RECEIPT);
        }
        if ($property == 'email_guest_created_expired') {
            return $this->getOption(GuestOptions::EMAIL_GUEST_EXPIRED);
        }
        if ($property == 'guest_upload_default_expire_is_guest_expire') {
            return $this->getOption(GuestOptions::GUEST_UPLOAD_DEFAULT_EXPIRE_IS_GUEST_EXPIRE);
        }
        if ($property == 'guest_upload_expire_read_only') {
            return $this->getOption(GuestOptions::GUEST_UPLOAD_EXPIRE_READ_ONLY);
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
    public function __set($property, $value)
    {
        if ($property == 'status') {
            $value = strtolower($value);
            if (!GuestStatuses::isValidValue($value)) {
                throw new GuestBadStatusException($value);
            }
            $this->status = (string)$value;
        } elseif ($property == 'user_email') {
            if (!Utilities::validateEmail($value)) {
                throw new BadEmailException($value);
            }
            $this->user_email = (string)$value;
        } elseif ($property == 'subject') {
            $this->subject = (string)$value;
        } elseif ($property == 'message') {
            $this->message = (string)$value;
        } elseif ($property == 'options') {
            $this->options = self::validateOptions($value);
        } elseif ($property == 'transfer_options') {
            $this->transfer_options = Transfer::validateOptions($value);
        } elseif ($property == 'transfer_count') {
            $this->transfer_count = (int)$value;
        } elseif ($property == 'email') {
            if (!Utilities::validateEmail($value)) {
                throw new BadEmailException($value);
            }
            $this->email = (string)$value;
        } elseif ($property == 'expires' || $property == 'last_activity') {

            if($property == 'expires' && $this->does_not_expire && is_null($value))
            {
                $this->$property = $value;
            }
            else
            {
                if (preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}$`', $value)) {
                    $value = strtotime($value);
                }
                
                if (!preg_match('`^[0-9]+$`', $value)) {
                    throw new BadExpireException($value);
                }
                $value = (int)$value;

                // The default could be set to very high if it is
                // set on a per user basis for their new guests
                // so if we are at the defualt then do not clamp.
                if( $value != $this->getDefaultExpire()) {
                    if ($value < floor(time() / (24 * 3600)) || $value > self::getMaxExpire()) {
                        throw new BadExpireException($value);
                    }
                }
                $this->$property = (string)$value;
            }
        } elseif ($property == 'service_aup_accepted_version') {
            $this->service_aup_accepted_version = $value;
        } elseif ($property == 'service_aup_accepted_time') {
            $this->service_aup_accepted_time = $value;
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
        return static::getClassName().'#'.($this->id ? $this->id : 'unsaved').'('.$this->email.')';
    }
}
