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
        'status' => array(
            'type' => 'string',
            'size' => 32
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'expires' => array(
            'type' => 'datetime'
        )
    );
    
    /**
     * Set selectors
     */
    const AVAILABLE = 'status = "available" ORDER BY created DESC';
    const EXPIRED = 'expires < DATE(NOW()) ORDER BY expires ASC';
    const FROM_USER = 'user_id = :user_id AND status = "available" ORDER BY created DESC';
    
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
    protected $created = 0;
    protected $expires = 0;
    
    /**
     * Cache
     */
    private $transfersCache = null;
    
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
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new GuestNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
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
        
        $guest->user_id = Auth::user()->id;
        $guest->__set('user_email', $from ? $from : Auth::user()->email[0]);
        $guest->__set('email', $recipient); // Throws
        
        $guest->status = GuestStatuses::AVAILABLE;
        $guest->created = time();
        
        // Generate token until it is indeed unique
        $guest->token = Utilities::generateUID(function($token) {
            $statement = DBI::prepare('SELECT * FROM '.Guest::getDBTable().' WHERE token = :token');
            $statement->execute(array(':token' => $token));
            $data = $statement->fetch();
            return !$data;
        });
        
        return $guest;
    }
    
    /**
     * Get max expire date
     * 
     * @return int timestamp
     */
    public static function getMaxExpire() {
        $days = Config::get('guest_default_daysvalid');
        if(!$days) $days = Config::get('default_daysvalid');
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get expired guests
     * 
     * @param integer $daysvalid guest age limit (optionnal)
     * 
     * @return array Guest list
     */
    public static function getExpired($daysvalid = null) {
        return self::all(self::EXPIRED);
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
        
        return self::all(self::FROM_USER, array(':user_id' => $user));
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
     * Check if user owns current transfer
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
        $this->save();
        
        Logger::logActivity(LogEventTypes::GUEST_CREATED, $this);
        
        $this->notify(true);
    }
    
    /**
     * Notify creation to the recipient
     * 
     * @param bool $just_created wether the notification is a reminder or the first one after creation
     */
    public function notify($just_created = false) {
        // Sending notification to recipient
        $c = Lang::translateEmail($just_created ? 'guest_created' : 'guest_reminder')->replace($this);
        $mail = new ApplicationMail($c);
        $mail->to($this->email);
        $mail->send();
        
        if($just_created) {
            // Sending receipt to owner
            $c = Lang::translateEmail('guest_created_receipt')->replace($this);
            $mail = new ApplicationMail($c);
            $mail->to($this->user_email);
            $mail->send();
        }
    }
    
    /**
     * Close the guest
     * 
     * @param bool $manually wether the guest was closed on request (if not it means it expired)
     */
    public function close($manualy = true) {
        // Closing the guest
        $this->status = GuestStatuses::CLOSED;
        $this->save();
        
        Logger::logActivity(
            $manualy ? LogEventTypes::GUEST_CLOSED : LogEventTypes::GUEST_EXPIRED,
            $this
        );
        
        // Sending notification to recipient
        $c = Lang::translateEmail($manualy ? 'guest_cancelled' : 'guest_expired')->replace($this);
        $mail = new ApplicationMail($c);
        $mail->to($this->email);
        $mail->send();
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
            'subject', 'message', 'options', 'status', 'created', 'expires'
        ))) return $this->$property;
        
        if($property == 'user' || $property == 'owner') {
            return User::fromId($this->user_id);
        }
        
        if($property == 'transfers') {
            if(is_null($this->transfersCache)) $this->transfersCache = Transfer::fromGuest($this);
            return $this->transfersCache;
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
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->user_email = (string)$value;
            
        }else if($property == 'subject') {
            $this->subject = (string)$value;
            
        }else if($property == 'message') {
            $this->message = (string)$value;
            
        }else if($property == 'options') {
            $this->options = $value;
            
        }else if($property == 'email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->email = (string)$value;
            
        }else if($property == 'expires') {
            if(preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}$`', $value)) {
                $value = strtotime($value);
            }
            
            if(!preg_match('`^[0-9]+$`', $value)) throw new BadExpireException($value);
            
            $value = (int)$value;
            if($value <= time() || $value > self::getMaxExpire()) {
                throw new BadExpireException($value);
            }
            $this->expires = (string)$value;
            
        }else throw new PropertyAccessException($this, $property);
    }
}
