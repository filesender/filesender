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
class User extends DBObject
{
    /**
     * Database table
     */
    protected static $dataTable = 'UserPreferences';
    
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
        'authid' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => false
        ),
        'additional_attributes' => array(
            'type' => 'text',
            'transform' => 'json',
            'null' => true
        ),
        'lang' => array(
            'type' => 'string',
            'size' => 8,
            'null' => true
        ),
        'aup_ticked' => array(
            'type' => 'bool'
        ),
        'aup_last_ticked_date' => array(
            'type' => 'date',
            'null' => true
        ),
        'transfer_preferences' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'guest_preferences' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'frequent_recipients' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'last_activity' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'auth_secret' => array(
            'type' => 'string',
            'size' => 64,
            'null' => true
        ),
        'auth_secret_created' => array(
            'type' => 'datetime',
            'null' => true
        ),
        'quota' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => true
        ),
        'guest_expiry_default_days' => array(
            'type' => 'uint',
            'size' => 'medium',
            'null' => true,
            'default' => null
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
        
        'save_frequent_email_address' => array(
            'type' => 'bool',
            'null'    => false,
            'default' => true,
        ),
        'save_transfer_preferences' => array(
            'type' => 'bool',
            'null'    => false,
            'default' => true,
        ),
    );


    public static function getViewMap()
    {
        $a = array();
        $userauthviewdef = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . DBView::columnDefinition_is_encrypted('transfer_preferences', 'prefers_enceyption')
                        . DBView::columnDefinition_age($dbtype, 'last_activity', 'last_activity_days_ago')
                        . DBView::columnDefinition_age($dbtype, 'aup_last_ticked_date', 'aup_last_ticked_days_ago')
                        . DBView::columnDefinition_age($dbtype, 'service_aup_accepted_time', 'service_aup_accepted_time_days_ago')
                        . ' , id as email_address '
                        . ' , id is not null as is_active '
                                . '  from ' . self::getDBTable();
            $userauthviewdef[$dbtype] = 'select up.id as id,authid,a.saml_user_identification_uid as user_id,up.last_activity,up.aup_ticked,up.created from '
                                       .self::getDBTable().' up, '.call_user_func('Authentication::getDBTable').' a where up.authid = a.id ';
        }
        
        
        return array( strtolower(self::getDBTable()) . 'view' => $a,
                      'userauthview' => $userauthviewdef
        );
    }
    
    /**
     * Properties
     */
    protected $id = null;
    protected $authid = null;
    protected $additional_attributes = null;
    protected $lang = null;
    protected $aup_ticked = false;
    protected $aup_last_ticked_date = 0;
    protected $transfer_preferences = null;
    protected $guest_preferences = null;
    protected $frequent_recipients = null;
    protected $created = 0;
    protected $last_activity = 0;
    protected $auth_secret = null;
    protected $auth_secret_created = null;
    protected $quota = 0;
    protected $guest_expiry_default_days = null;
    protected $service_aup_accepted_version = 0;
    protected $service_aup_accepted_time = null;
    protected $save_frequent_email_address = true;
    protected $save_transfer_preferences = true;

    
    /** 
     * These are not real properties and are used by queries in the
     * system to return additional data about a user from the query.
     */
    private $eventcount = 0;
    private $eventip = 0;
    
    /**
     * From Auth if it makes sense
     */
    private $email_addresses = array();
    private $name = null;
    
    /**
     * Misc
     */
    private $hasPreferences = false;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the user from (if already fetched from database)
     *
     * @throws UserNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            $this->id = $id;
        }
        
        if ($data) {
            // Fill properties from provided data
            $this->fillFromDBData($data);
            $this->hasPreferences = true;
        } else {
            // New user, set base data
            $this->id = $id;
            $this->created = time();
        }
        
        // Generate user remote auth secret
        if (Config::get('auth_remote_user_autogenerate_secret') && !$this->auth_secret) {
            // do not auto generate if the user must accept aup
            if( !Config::get('api_secret_aup_enabled')) {
                $this->authSecretCreate();
            }
        }
    }

    public function authSecretCreate() {
        $this->auth_secret = hash('sha256', $this->id.'|'.time().'|'.Utilities::generateUID());
        $this->auth_secret_created = time();
        $this->save();
    }
    public function authSecretDelete() {
        $this->auth_secret = null;
        $this->auth_secret_created = null;
        $this->save();
    }
    public static function authSecretDeleteAll() {
        $statement = DBI::prepare('update '.self::getDBTable().' set auth_secret_created=null, auth_secret=null ');
        $statement->execute(array());
    }
    
    /**
     * Loads user from Auth attributes, handling cache
     *
     * @param string $attributes
     *
     * @return User
     */
    public static function fromAttributes($attributes)
    {
        // Check if uid attribute exists
        if (!is_array($attributes) || !array_key_exists('uid', $attributes) || !$attributes['uid']) {
            throw new UserMissingUIDException();
        }
        
        // Get matching user
        $authid = Authentication::ensureAuthIDFromSAMLUID($attributes['uid']);
        $user = self::fromAuthId($authid);
        
        // Add metadata from attributes
        if (array_key_exists('email', $attributes)) {
            $user->email_addresses = (array) $attributes['email'];
        }
        if (array_key_exists('name', $attributes)) {
            $user->name = $attributes['name'];
        }

        
        return $user;
    }

    public static function fromAuthId($authid)
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE authid = :authid');
        $statement->execute(array(':authid' => $authid));
        $data = $statement->fetch();

        if (!$data) {
            $data = array();
            $data['authid'] = $authid;
            $ret = static::createFactory(null, $data);
            $ret->created = time();
            $ret->authid = $authid;
            $ret->insert();
            return $ret;
        }
        $id = $data['id'];
//        Logger::info('fromAuthId() found authid ' . $authid . ' at id ' . $id );
        return self::fromId($id);
    }
    
    /**
     * Save user preferences in database
     */
    public function customSave()
    {
        if ($this->hasPreferences) {
            // Was loaded from existing record, update it
            $this->updateRecord($this->toDBData(), 'id');
        } else {
            // Has no existing record in database, create it
            $this->insertRecord($this->toDBData());
            $this->hasPreferences = true;
            Logger::logActivity(LogEventTypes::USER_CREATED, $this);
        }
    }
    
    /**
     * Create a new user
     *
     * @param string $id user id, mandatory
     *
     * @return User
     */
    public static function create($id)
    {
        return self::fromId($id);
    }
    
    /**
     * Record activity
     */
    public function recordActivity($forceSave = false)
    {
        $now = time();
        
        // Do not record more than once per 1h => reduces number of writes
        if (!$forceSave) {
            if (abs($now - $this->last_activity) < 3600) {
                return;
            }
        }
        
        $this->last_activity = $now;
        $this->save();
    }
    
    /**
     * Search users
     *
     * @param string $match
     *
     * @return self[]
     */
    public static function search($match)
    {
        // Remove to-be-used escape char
        $match = str_replace('\\', '', $match);
        
        // Escape special chars
        $match = str_replace(array('%', '_'), array('\\%', '\\_'), $match);

        $escapeClause = '';
        if( DBLayer::isMySQL() ) {
            $escapeClause = " ESCAPE '\\\\' ";
        }
        $sql = "select u.* from "
             . User::getDBTable() . " u,"
             . " " . Authentication::getDBTable()
             . " a where a.id = u.authid and a.saml_user_identification_uid like :match " . $escapeClause;
        $statement = DBI::prepare($sql);
        $placeholders =  array(':match' => '%'.$match.'%');
        $statement->execute($placeholders);
        $records = $statement->fetchAll();
        return self::convertTableResultsToObjects($records);
    }
    
    /**
     * Get active users
     *
     * @return array of User
     */
    public static function getActive()
    {
        $days = Config::get('user_active_days');
        
        if (!$days || !is_int($days) || $days <= 0) {
            $days = Config::get('user_inactive_days');
            
            if (!$days || !is_int($days) || $days <= 0) {
                $days = 30;
            }
        }
        
        return User::all('last_activity >= :date', array(':date' => date('Y-m-d', time() - $days * 24 * 3600)));
    }
    
    /**
     * Remove inactive users preferences
     */
    public static function removeInactive()
    {
        $days = Config::get('user_inactive_days');
        if (!$days || !is_int($days) || $days <= 0) {
            return;
        }
        
        foreach (User::all('last_activity < :date', array(':date' => date('Y-m-d', time() - $days * 24 * 3600))) as $user) {
            $user->delete();
        } // No need to remove transfers and guests as only saved preferences are deleted (not user account which is managed by identity federation)
    }
    
    /**
     * This function allows to get the frequent recipients of the current user.
     * If $criteria is set, get all recipients matching the criteria
     *
     * @param String $criteria: criteria to search on
     * @return array: list of frequent recipients
     */
    public function getFrequentRecipients($criteria = null)
    {
        if( Config::get('data_protection_user_frequent_email_address_disabled')) {
            return array();
        }
        if( !$this->save_frequent_email_address ) {
            return array();
        }

        // Get max number of returned recipients from config
        $size = Config::get('autocomplete');
        if (!$size || !is_int($size) || $size <= 0) {
            return array();
        }
        
        // Get recipients from preferences
        $recipients = $this->frequent_recipients;
        if (!$recipients) {
            $recipients = array();
        }
        
        // Filter if requested
        if ($criteria) {
            $recipients = array_filter($recipients, function ($recipient) use ($criteria) {
                return stripos($recipient->email, $criteria) !== false;
            });
        }
        
        // Return the right amount
        return array_map(function ($recipient) {
            return $recipient->email;
        }, array_slice($recipients, 0, $size));
    }
    
    /**
     * This function allows to save frequent recipients
     *
     * @param array $mails: mails to save
     * @return boolean true if saved successfuly, false otherwise
     */
    public function saveFrequentRecipients($mails = array())
    {
        // Get already set recipients
        $recipients = $this->frequent_recipients;
        if (!$recipients) {
            $recipients = array();
        }
        
        // Process given recipients
        foreach ($mails as $mail) {
            // Cast if needed
            if ($mail instanceof Recipient) {
                $mail = $mail->email;
            }
            
            // Remove if already in list
            $recipients = array_filter($recipients, function ($recipient) use ($mail) {
                return $recipient->email != $mail;
            });
            
            // Add in front of the list
            array_unshift($recipients, (object)array('email' => $mail, 'date' => time()));
        }
        
        // Limit number of stored recipients depending on config
        $size = 0;
        $cnt = Config::get('autocomplete');
        $pool = Config::get('autocomplete_max_pool');
        
        if (is_int($cnt) && $cnt > 0) {
            if (is_int($pool) && $pool > 0) {
                $size = $pool;
            } else {
                $size = 5 * $cnt;
            }
        }
        
        $recipients = $size ? array_slice($recipients, 0, $size) : array();

        // wipe it out if that is what the admin wants.
        if( Config::get('data_protection_user_frequent_email_address_disabled')) {
            $recipients = array();
        }
        if( !$this->save_frequent_email_address ) {
            $recipients = array();
        }

        
        // Save if something changed
        if ($recipients !== $this->frequent_recipients) {
            $this->frequent_recipients = $recipients;
            $this->save();
        }
    }
    
    /**
     * Save choosen options
     *
     * @param string $target "transfer" or "guest"
     * @param array $options
     */
    private function saveOptions($target, $options = array())
    {
        $prop = $target.'_preferences';
        if (!property_exists($this, $prop)) {
            return;
        }
        
        $prefs = $this->$prop ? (array)$this->$prop : array();
        
        // Analyse options
        foreach (Transfer::allOptions() as $name => $dfn) {
            if (
                isset($options[TransferOptions::GET_A_LINK]) &&
                in_array($name, array(
                        TransferOptions::EMAIL_ME_COPIES,
                        TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE,
                        TransferOptions::ADD_ME_TO_RECIPIENTS
                ))
            ) {
                continue;
            }
            
            if ($dfn['available']) {
                if (!array_key_exists($name, $prefs)) {
                    $prefs[$name] = 0;
                }
                
                $default = $this->defaultOptionState($target, $name);
                
                if (array_key_exists($name, $options) && $options[$name] == $default) {
                    continue;
                } // User did not change what we proposed
                if (!$default && !array_key_exists($name, $options)) {
                    continue;
                } // Option doesn't exist, assume false - user choose false, too
                
                $prefs[$name] += array_key_exists($name, $options) && $options[$name]!=null ? 1 : -1;
            } else { // Remove options that are not available (anymore) from prefs
                if (array_key_exists($name, $prefs)) {
                    unset($prefs[$name]);
                }
            }
        }
        
        $prefs = array_filter($prefs);
        
        // Save if something changed
        if ($prefs !== $this->$prop) {
            $this->$prop = $prefs;
            $this->save();
        }
    }
    
    /**
     * Save choosen transfer options
     *
     * @param array $options
     */
    public function saveTransferOptions($options = array())
    {
        $this->saveOptions('transfer', $options);
    }
    
    /**
     * Save choosen guest options
     *
     * @param array $options
     */
    public function saveGuestOptions($options = array())
    {
        $this->saveOptions('guest', $options);
    }
    
    /**
     * Get defaut state for option
     *
     * @param string $target "transfer" or "guest"
     * @param string $option
     *
     * @return bool
     */
    private function defaultOptionState($target, $option)
    {
        $defaults = call_user_func(ucfirst($target).'::availableOptions');
        
        $default = array_key_exists($option, $defaults) ? $defaults[$option]['default'] : false;
        $prop = $target.'_preferences';
        $props = $this->$prop;
        $props = (object)$props;
        
        if (
            !property_exists($this, $prop)
            || !$this->$prop
            || !property_exists($props, $option)
        ) {
            return $default;
        }
        
        $score = $props->$option;

        if( $this->save_transfer_preferences ) {
            return $score;
        }
        
        if (abs($score) < 3) {
            return $default;
        }
        
        return $score > 0;
    }
    
    /**
     * Get defaut state for transfer option
     *
     * @param string $option
     *
     * @return bool
     */
    public function defaultTransferOptionState($option)
    {
        return $this->defaultOptionState('transfer', $option);
    }
    
    /**
     * Get defaut state for guest option
     *
     * @param string $option
     *
     * @return bool
     */
    public function defaultGuestOptionState($option)
    {
        return $this->defaultOptionState('guest', $option);
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
            'id','additional_attributes', 'lang', 'aup_ticked', 'aup_last_ticked_date', 'auth_secret',
            'auth_secret_created',
            'transfer_preferences', 'guest_preferences', 'frequent_recipients', 'created', 'last_activity',
            'email_addresses', 'name', 'quota', 'authid'
          , 'guest_expiry_default_days', 'service_aup_accepted_version', 'service_aup_accepted_time'
          , 'save_frequent_email_address', 'save_transfer_preferences'
            
        ))) {
            return $this->$property;
        }

        if( $property == 'auth_secret_created_formatted' ) {
            return $this->auth_secret_created ? Utilities::formatDate($this->auth_secret_created,true) : '';
        }

        if ($property == 'saml_user_identification_uid') {
            $a = Authentication::fromId($this->authid);
            return $a->saml_user_identification_uid;
        }
        
        if ($property == 'email') {
            return count($this->email_addresses) ? $this->email_addresses[0] : null;
        }
        
        if ($property == 'remote_config') {
            return $this->auth_secret ? Config::get('site_url').'|'.$this->id.'|'.$this->auth_secret : '';
        }
        
        if ($property == 'identity') {
            return $this->email;
        }

        if ($property == 'eventcount') {
            return $this->eventcount;
        }
        if ($property == 'eventip') {
            return $this->eventip;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     *
     * @throws BadVoucherException
     * @throws BadStatusException
     * @throws BadExpireException
     * @throws PropertyAccessException
     */
    public function __set($property, $value)
    {
        if ($property == 'additional_attributes') {
            $this->additional_attributes = (object)(array)$value;
        } elseif ($property == 'lang') {
            if (!array_key_exists($value, Lang::getAvailableLanguages())) {
                throw new BadLangCodeException($value);
            }
            $this->lang = (string)$value;
        } elseif ($property == 'aup_ticked') {
            $this->aup_ticked = (bool)$value;
        } elseif ($property == 'transfer_preferences') {
            $this->transfer_preferences = $value;
        } elseif ($property == 'guest_preferences') {
            $this->guest_preferences = $value;
        } elseif ($property == 'frequent_recipients') {
            if( Config::get('data_protection_user_frequent_email_address_disabled') || !$this->save_frequent_email_address ) {
                // keep nothing.
                $this->frequent_recipients = array();
            } else {
                $this->frequent_recipients = $value;
            }
        } elseif ($property == 'email_addresses') {
            if (!is_array($value)) {
                $value = array($value);
            }
            foreach ($value as $email) {
                if (!Utilities::validateEmail($email)) {
                    throw new BadEmailException($value);
                }
            }
            $this->email_addresses = $value;
        } elseif ($property == 'name') {
            $this->name = (string)$value;
        } elseif ($property == 'quota') {
            $this->quota = (int)$value;
        } elseif ($property == 'eventcount') {
            $this->eventcount = (int)$value;
        } elseif ($property == 'eventip') {
            $this->eventip = $value;
        } elseif ($property == 'guest_expiry_default_days') {
            $this->guest_expiry_default_days = (int)$value;
            if( $this->guest_expiry_default_days == 0 ) {
                $this->guest_expiry_default_days = null;
            }
        } elseif ($property == 'service_aup_accepted_version') {
            $this->service_aup_accepted_version = $value;
        } elseif ($property == 'service_aup_accepted_time') {
            $this->service_aup_accepted_time = $value;
        } elseif ($property == 'save_frequent_email_address') {
            $this->save_frequent_email_address = $value;
        } elseif ($property == 'save_transfer_preferences') {
            $this->save_transfer_preferences = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }

    

    /**
     * Delete the user related objects that the database delete will not remove.
     * for example, all the files on the disk for transfers owned by this user
     * or their guests.
     */
    public function beforeDelete()
    {
        $user = $this;
        $transfers = Transfer::fromGuestsOf($user,false);
        foreach ($transfers as $t) {
            $t->delete();
        }
        $transfers = Transfer::fromUser($user);
        foreach ($transfers as $t) {
            $t->delete();
        }

        // The RI from translatable emails to guests is not 100%
        // so we have to remove the guests manually to also get that
        // associated information
        $guests = Guest::fromUser($user);
        foreach ($guests as $g) {
            $g->delete();
        }
    }

    public function beforeSave()
    {
        if( Config::get('data_protection_user_frequent_email_address_disabled')) {
            $this->frequent_recipients = array();
        }
        if( !$this->save_frequent_email_address ) {
            $this->frequent_recipients = array();
        }
        Logger::dump("AAA beforesave ", $this->transfer_preferences );
        if( Config::get('data_protection_user_transfer_preferences_disabled')) {
            $this->transfer_preferences = null;
        }
        if( !$this->save_transfer_preferences ) {
            $this->transfer_preferences = null;
        }
    }

    public function remindLocalAuthDBPassword( $password )
    {
        $user = $this;
        TranslatableEmail::quickSend('local_authdb_password_reminder', $user, array('password' => $password));
    }

    public function exportMyData()
    {
        $user = $this;
        $ret = array();

        $statement = DBI::prepare('SELECT * FROM '.User::getDBTable().' WHERE id = :id');
        $statement->execute(array(':id' => $user->id));
        $ret['user'] = $statement->fetch();

        //
        // My guests
        //
        $ret['guests'] = array();
        $guests = Guest::fromUser($user);
        foreach ($guests as $g) {
            $id = $g->id;
            $ret['guests'][$id] = $g;
        }

        //
        // My transfers
        //
        $ret['transfers'] = array();
        $statement = DBI::prepare('SELECT * FROM '.Transfer::getDBTable().' WHERE userid = :id and ( guest_id is null or guest_transfer_shown_to_user_who_invited_guest )');
        $statement->execute(array(':id' => $user->id));
        $records = $statement->fetchAll();
        foreach ($records as $r) {
            $tid = $r['id'];
            $ret['transfers'][$tid] = $r;
            $transfer = Transfer::fromId($tid);

            //
            // Files in this transfer
            //
            $files = File::fromTransfer( $transfer );
            foreach ($files as $file) {
                $ret['transfers'][$tid]['file'] = $file;
            }

            //
            // AuditLogs
            //
            foreach (AuditLog::fromTransfer($transfer) as $log) {
                $lid = $log->id;
                $ret['transfers'][$tid]['log'][$lid] = $log;
            }

            //
            // Recipients
            //
            foreach ($transfer->recipients as $recipient) {
                $rid = $recipient->id;
                $ret['transfers'][$tid]['recipient'][$rid] = $recipient;
            }

            //
            // Emails
            //
            foreach (TranslatableEmail::fromContext($transfer) as $translatable_email) {
                $id = $translatable_email->id;
                $ret['transfers'][$tid]['translated_email'][$id] = $translatable_email;
            }
        }
        

        return $ret;
    }

}
