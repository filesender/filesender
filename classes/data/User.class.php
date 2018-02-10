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
class User extends DBObject {
    /**
     * Database table
     */
    protected static $dataTable = 'UserPreferences';
    
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'string',
            'size' => 190,
            'primary' => true
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
        'quota' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => true
        ),
    );
    
    /**
     * Properties
     */
    protected $id = null;
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
    protected $quota = 0;
    
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
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
        }
        
        if($data) {
            // Fill properties from provided data
            $this->fillFromDBData($data);
            $this->hasPreferences = true;
            
        }else{
            // New user, set base data
            $this->id = $id;
            $this->created = time();
        }
        
        // Generate user remote auth secret 
        if(Config::get('auth_remote_user_autogenerate_secret') && !$this->auth_secret) {
            $this->auth_secret = hash('sha256', $this->id.'|'.time().'|'.Utilities::generateUID());
            $this->save();
        }
    }
    
    /**
     * Loads user from Auth attributes, handling cache
     * 
     * @param string $attributes
     * 
     * @return User
     */
    public static function fromAttributes($attributes) {
        // Check if uid attribute exists
        if(!is_array($attributes) || !array_key_exists('uid', $attributes) || !$attributes['uid']) throw new UserMissingUIDException();
        
        // Get matching user
        $user = self::fromId($attributes['uid']);
        
        // Add metadata from attributes
        if(array_key_exists('email', $attributes)) $user->email_addresses = (array) $attributes['email'];
        if(array_key_exists('name', $attributes)) $user->name = $attributes['name'];
        
        return $user;
    }
    
    /**
     * Save user preferences in database
     */
    public function customSave() {
        if($this->hasPreferences) {
            // Was loaded from existing record, update it
            $this->updateRecord($this->toDBData(), 'id');
            
        }else{
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
    public static function create($id) {
        return self::fromId($id);
    }
    
    /**
     * Record activity
     */
    public function recordActivity() {
        $now = time();
        
        // Do not record more than once per 1h => reduces number of writes
        if(abs($now - $this->last_activity) < 3600) return;
        
        $this->last_activity = $now;
        $this->save();
    }
    
    /**
     * Get active users
     * 
     * @return array of User
     */
    public static function getActive() {
        $days = Config::get('user_active_days');
        
        if(!$days || !is_int($days) || $days <= 0) {
            $days = Config::get('user_inactive_days');
            
            if(!$days || !is_int($days) || $days <= 0)
                $days = 30;
        }
        
        return User::all('last_activity >= :date', array(':date' => date('Y-m-d', time() - $days * 24 * 3600)));
    }
    
    /**
     * Remove inactive users preferences
     */
    public static function removeInactive() {
        $days = Config::get('user_inactive_days');
        if(!$days || !is_int($days) || $days <= 0)
            return;
        
        foreach(User::all('last_activity < :date', array(':date' => date('Y-m-d', time() - $days * 24 * 3600))) as $user)
            $user->delete(); // No need to remove transfers and guests as only saved preferences are deleted (not user account which is managed by identity federation)
    }
    
    /**
     * This function allows to get the frequent recipients of the current user.
     * If $criteria is set, get all recipients matching the criteria
     * 
     * @param String $criteria: criteria to search on
     * @return array: list of frequent recipients
     */
    public function getFrequentRecipients($criteria = null) {
        // Get max number of returned recipients from config
        $size = Config::get('autocomplete');
        if(!$size || !is_int($size) || $size <= 0) return array();
        
        // Get recipients from preferences
        $recipients = $this->frequent_recipients;
        if(!$recipients) $recipients = array();
        
        // Filter if requested
        if($criteria) $recipients = array_filter($recipients, function($recipient) use($criteria) {
            return strpos($recipient->email, $criteria) !== false;
        });
        
        // Return the right amount
        return array_map(function($recipient) {
            return $recipient->email;
        }, array_slice($recipients, 0, $size));
    }
    
    /**
     * This function allows to save frequent recipients
     * 
     * @param array $mails: mails to save
     * @return boolean true if saved successfuly, false otherwise
     */
    public function saveFrequentRecipients($mails = array()) {
        // Get already set recipients
        $recipients = $this->frequent_recipients;
        if(!$recipients) $recipients = array();
        
        // Process given recipients
        foreach($mails as $mail) {
            // Cast if needed
            if($mail instanceof Recipient) $mail = $mail->email;
            
            // Remove if already in list
            $recipients = array_filter($recipients, function($recipient) use($mail) {
                return $recipient->email != $mail;
            });
            
            // Add in front of the list
            array_unshift($recipients, (object)array('email' => $mail, 'date' => time()));
        }
        
        // Limit number of stored recipients depending on config
        $size = 0;
        $cnt = Config::get('autocomplete');
        $pool = Config::get('autocomplete_max_pool');
        
        if(is_int($cnt) && $cnt > 0) {
            if(is_int($pool) && $pool > 0) {
                $size = $pool;
            } else {
                $size = 5 * $cnt;
            }
        }
        
        $recipients = $size ? array_slice($recipients, 0, $size) : array();
        
        // Save if something changed
        if($recipients !== $this->frequent_recipients) {
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
    private function saveOptions($target, $options = array()) {
        $prop = $target.'_preferences';
        if(!property_exists($this, $prop)) return;
        
        $prefs = $this->$prop ? (array)$this->$prop : array();
        
        // Analyse options
        foreach(Transfer::allOptions() as $name => $dfn) {
            if(
                isset($options[TransferOptions::GET_A_LINK]) && 
                in_array($name, array(
                        TransferOptions::EMAIL_ME_COPIES,
                        TransferOptions::ENABLE_RECIPIENT_EMAIL_DOWNLOAD_COMPLETE,
                        TransferOptions::ADD_ME_TO_RECIPIENTS
                ))
            ) continue;
            
            if($dfn['available']) {
                if(!array_key_exists($name, $prefs))
                    $prefs[$name] = 0;
                
                $default = $this->defaultOptionState($target, $name);
                
                if(array_key_exists($name, $options) && $options[$name] == $default)
                    continue; // User did not change what we proposed
                if(!$default && !array_key_exists($name, $options))
                    continue; // Option doesn't exist, assume false - user choose false, too
                
                $prefs[$name] += array_key_exists($name, $options) && $options[$name]!=null ? 1 : -1;
                
            } else { // Remove options that are not available (anymore) from prefs
                if(array_key_exists($name, $prefs))
                    unset($prefs[$name]);
            }
        }
        
        $prefs = array_filter($prefs);
        
        // Save if something changed
        if($prefs !== $this->$prop) {
            $this->$prop = $prefs;
            $this->save();
        }
    }
    
    /**
     * Save choosen transfer options
     * 
     * @param array $options
     */
    public function saveTransferOptions($options = array()) {
        $this->saveOptions('transfer', $options);
    }
    
    /**
     * Save choosen guest options
     * 
     * @param array $options
     */
    public function saveGuestOptions($options = array()) {
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
    private function defaultOptionState($target, $option) {
        $defaults = call_user_func(ucfirst($target).'::availableOptions');
        
        $default = array_key_exists($option, $defaults) ? $defaults[$option]['default'] : false;
        $prop = $target.'_preferences';
        $props = $this->$prop;
        $props = (object)$props;
        
        if(
            !property_exists($this, $prop)
            || !$this->$prop
            || !property_exists($props, $option)
        ) return $default;
        
        $score = $props->$option;
        
        if(abs($score) < 3)
            return $default;
        
        return $score > 0;
    }
    
    /**
     * Get defaut state for transfer option
     * 
     * @param string $option
     * 
     * @return bool
     */
    public function defaultTransferOptionState($option) {
        return $this->defaultOptionState('transfer', $option);
    }
    
    /**
     * Get defaut state for guest option
     * 
     * @param string $option
     * 
     * @return bool
     */
    public function defaultGuestOptionState($option) {
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
    public function __get($property) {
        if(in_array($property, array(
            'id', 'additional_attributes', 'lang', 'aup_ticked', 'aup_last_ticked_date', 'auth_secret',
            'transfer_preferences', 'guest_preferences', 'frequent_recipients', 'created', 'last_activity',
            'email_addresses', 'name', 'quota'
        ))) return $this->$property;
        
        if($property == 'email') return count($this->email_addresses) ? $this->email_addresses[0] : null;
        
        if($property == 'remote_config') return $this->auth_secret ? Config::get('site_url').'|'.$this->id.'|'.$this->auth_secret : '';
        
        if($property == 'identity') return $this->email;
        
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
    public function __set($property, $value) {
        if($property == 'additional_attributes') {
            $this->additional_attributes = (object)(array)$value;
            
        }else if($property == 'lang') {
            if(!array_key_exists($value, Lang::getAvailableLanguages()))
                throw new BadLangCodeException($value);
            $this->lang = (string)$value;
            
        }else if($property == 'aup_ticked') {
            $this->aup_ticked = (bool)$value;
            
        }else if($property == 'transfer_preferences') {
            $this->transfer_preferences = $value;
            
        }else if($property == 'guest_preferences') {
            $this->guest_preferences = $value;
            
        }else if($property == 'frequent_recipients'){
            $this->frequent_recipients = $value;
            
        }else if($property == 'email_addresses') {
            if(!is_array($value)) $value = array($value);
            foreach($value as $email)
                if(!Utilities::validateEmail($email))
                    throw new BadEmailException($value);
            $this->email_addresses = $value;
            
        }else if($property == 'name') {
            $this->name = (string)$value;
            
        }else if($property == 'quota') {
            $this->quota = (int)$value;
            
        }else throw new PropertyAccessException($this, $property);
    }
}
