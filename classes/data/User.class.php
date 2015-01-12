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
            'size' => 255,
            'primary' => true
        ),
        'organization' => array(
            'type' => 'string',
            'size' => 80,
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
    );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $organization = null;
    protected $lang = null;
    protected $aup_ticked = false;
    protected $aup_last_ticked_date = 0;
    protected $transfer_preferences = null;
    protected $guest_preferences = null;
    protected $frequent_recipients = null;
    protected $created = 0;
    protected $last_activity = 0;
    protected $auth_secret = null;
    
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
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
        }
        
        if($data) {
            $this->fillFromDBData($data);
            $this->hasPreferences = true;
        }else{
            $this->id = $id;
            $this->created = time();
        }
        
        if(Config::get('auth_remote_user_autogenerate_secret') && !$this->auth_secret)
            $this->auth_secret = hash('sha256', $this->id.'|'.time().'|'.Utilities::generateUID());
    }
    
    /**
     * Loads user from Auth attributes, handling cache
     * 
     * @param string $attributes
     * 
     * @return User
     */
    public static function fromAttributes($attributes) {
        if(!is_array($attributes) || !array_key_exists('uid', $attributes) || !$attributes['uid']) throw new UserMissingUIDException();
        $user = self::fromId($attributes['uid']);
        
        if(array_key_exists('email', $attributes)) $user->email_addresses = is_array($attributes['email']) ? $attributes['email'] : array($attributes['email']);
        if(array_key_exists('name', $attributes)) $user->name = $attributes['name'];
        
        return $user;
    }
    
    /**
     * Save user preferences in database
     */
    public function customSave() {
        if($this->hasPreferences) {
            $this->updateRecord($this->toDBData(), 'id');
        }else{
            $this->insertRecord($this->toDBData());
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
     * Report last activity
     */
    public function reportActivity() {
        $this->last_activity = time();
        $this->save();
    }
    
    
    /**
     * This function allows to get the frequent recipients of the current user.
     * If $criteria is set, get all recipients matching the criteria
     * 
     * @param String $criteria: criteria to search on
     * @return array: list of frequent recipients
     */
    public function getFrequentRecipients($criteria = null) {
        $maxAllowed = Config::get('autocomplete_max_shown');
        if(!$maxAllowed) $maxAllowed = 5;
        
        $recipients = $this->frequent_recipients;
        if(!$recipients) $recipients = array();
        
        if($criteria) $recipients = array_filter($recipients, function($recipient) use($criteria) {
            return strpos($recipient->email, $criteria) !== false;
        });
        
        return array_map(function($recipient) {
            return $recipient->email;
        }, array_slice($recipients, 0, $maxAllowed));
    }
    
    /**
     * This function allows to save frequent recipients
     * 
     * @param array $mails: mails to save
     * @return boolean true if saved successfuly, false otherwise
     */
    public function saveFrequentRecipients($mails = array()){
        $recipients = $this->frequent_recipients;
        if(!$recipients) $recipients = array();
        
        foreach($mails as $mail) {
            if($mail instanceof Recipient) $mail = $mail->email;
            
            $recipients = array_filter($recipients, function($recipient) use($mail) {
                return $recipient->email != $mail;
            });
            
            array_unshift($recipients, (object)array('email' => $mail, 'date' => time()));
        }
        
        $maxStored = Config::get('max_stored_frequent_recipients');
        if(!$maxStored) $maxStored = 0;
        $recipients = array_slice($recipients, 0, $maxStored);
        
        if($recipients !== $this->frequent_recipients) {
            $this->frequent_recipients = $recipients;
            $this->save();
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
    public function __get($property) {
        if(in_array($property, array(
            'id', 'organization', 'lang', 'aup_ticked', 'aup_last_ticked_date', 'auth_secret',
            'transfer_preferences', 'guest_preferences', 'frequent_recipients', 'created', 'last_activity',
            'email_addresses', 'name'
        ))) return $this->$property;
        
        if($property == 'email') return count($this->email_addresses) ? $this->email_addresses[0] : null;
        
        if($property == 'remote_config') return $this->auth_secret ? Config::get('site_url').'|'.$this->id.'|'.$this->auth_secret : '';
        
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
        if($property == 'organization') {
            $this->organization = (string)$value;
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
                if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    throw new BadEmailException($value);
            $this->email_addresses = $value;
        }else if($property == 'name') {
            $this->name = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
}
