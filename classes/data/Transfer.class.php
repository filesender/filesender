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
 * Represents a transfer in database
 * 
 * @property array $filesCache related filesCache
 * @property array $recipientsCache related recipientsCache
 */
class Transfer extends DBObject {
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
            'size' => 250
        ),
        'user_email' => array(
            'type' => 'string',
            'size' => 250
        ),
        'guest_id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'null' => true
        ),
        'subject' => array(
            'type' => 'string',
            'size' => 250,
            'null' => true
        ),
        'message' => array(
            'type' => 'text',
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime'
        ),
        'expires' => array(
            'type' => 'datetime'
        ),
        'status' => array(
            'type' => 'string',
            'size' => 32
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        )
    );
    
    /**
     * Set selectors
     */
    const AVAILABLE = 'status = "available" ORDER BY created DESC';
    const EXPIRED = 'expires < DATE(NOW()) ORDER BY expires ASC';
    const FROM_USER = 'user_id = :user_id AND status="available" ORDER BY created DESC';
    const FROM_GUEST = 'guest_id = :guest_id AND status="available" ORDER BY created DESC';
    
    /**
     * Properties
     */
    protected $id = null;
    protected $status = null;
    protected $user_id = null;
    protected $user_email = null;
    protected $guest_id = null;
    protected $subject = null;
    protected $message = null;
    protected $created = 0;
    protected $expires = 0;
    protected $options = null;
    
    /**
     * Related objects cache
     */
    private $filesCache = null;
    private $recipientsCache = null;
    
    /**
     * Constructor
     * 
     * @param integer $id identifier of transfer to load from database (null if loading not wanted)
     * @param array $data data to create the transfer from (if already fetched from database)
     * 
     * @throws TransferNotFoundException
     */
    protected function __construct($id = null, $data = null) {
        if(!is_null($id)) {
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if(!$data) throw new TransferNotFoundException('id = '.$id);
        }
        
        if($data) $this->fillFromDBData($data);
    }
    
    /**
     * Get transfers from user
     * 
     * @param mixed $user User or user id
     * 
     * @return array of Transfer
     */
    public static function fromUser($user) {
        if($user instanceof User) $user = $user->id;
        
        return self::all(self::FROM_USER, array(':user_id' => $user));
    }
    
    /**
     * Get transfers from guest
     * 
     * @param mixed $guest Guest or Guest id
     * 
     * @return array of Transfer
     */
    public static function fromGuest($guest) {
        if($guest instanceof Guest) $guest = $guest->id;
        
        return self::all(self::FROM_GUEST, array(':guest_id' => $guest));
    }
    
    /**
     * Get transfers from guests of user
     * 
     * @param mixed $user User or user id
     * 
     * @return array of Transfer
     */
    public static function fromGuestsOf($user) {
        if($user instanceof User) $user = $user->id;
        
        $transfers = array();
        foreach(Guest::fromUser($user) as $gv) {
            $transfers = array_merge($transfers, $gv->transfers);
        }
        
        uasort($transfers, function($a, $b) {
            return $a->created - $b->created;
        });
        
        return $transfers;
    }
    
    /**
     * Create a new transfer (ie begin upload)
     * 
     * @param integer $expiry expiration date (timestamp), mandatory
     * @param string $user_email sender's email (multiple user emails handling)
     * 
     * @return Transfer
     */
    public static function create($expires, $user_email = null) {
        $transfer = new self();
        
        $transfer->user_id = Auth::user()->id;
        
        if(Auth::isGuest())
            $transfer->guest = AuthGuest::getGuest();
        
        if(!$user_email) $user_email = Auth::user()->email[0];
        if(!in_array($user_email, Auth::user()->email))
            throw new BadEmailException($user_email);
        
        $transfer->__set('user_email', $user_email);
        
        $transfer->__set('expires', $expires);
        
        $transfer->created = time();
        $transfer->status = 'uploading';
        
        return $transfer;
    }
    
    /**
     * Get max expire date
     * 
     * @return int timestamp
     */
    public static function getMaxExpire() {
        return strtotime('+'.Config::get('default_daysvalid').' day');
    }
    
    /**
     * Delete the transfer related objects
     */
    public function beforeDelete() {
        AuditLog::clean($this);
        
        foreach($this->files as $file) $this->removeFile($file);
        
        foreach($this->recipients as $recipient) $this->removeRecipient($recipient);
    }
    
    /**
     * Close the transfer
     */
    public function close($manualy = true) {
        if($this->status != TransferStatuses::AVAILABLE) { // Simple deletion if the transfer was not available yet (failed, cancelled ...)
            $this->delete();
            return;
        }
        
        // Close the transfer
        $this->status = TransferStatuses::CLOSED;
        $this->save();
        
        // Log action
        Logger::logActivity($manualy ? LogEventTypes::TRANSFER_CLOSED : LogEventTypes::TRANSFER_EXPIRED, $this);

        // Send notification to all recipients 
        $ctn = Lang::translateEmail($manualy ? 'transfer_deleted' : 'transfer_expired')->r($this);
        
        foreach($this->recipients as $recipient) {
            $mail = new ApplicationMail($ctn->r($recipient));
            $mail->send();
        }
        
        // Send notification to owner
        $ctn = Lang::translateEmail($manualy ? 'transfer_deleted_receipt' : 'transfer_expired_receipt')->r($this);
        $mail = new ApplicationMail($ctn);
        $mail->send();
        
        // Generating the repport for the transfer owner if requested
        if($this->hasOption(TransferOptions::EMAIL_REPORT_ON_CLOSING)) {
            $format = Config::get('report_format');
            if(ReportTypes::isValidName($type)) {
                $report = new Report($type, $this);
                $report->generateReport(true); // Send by email
            }
        }
        
        // Delete transfer if needed
        if(!Config::get('auditlog_lifetime'))
            $this->delete();
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
     * Get all options
     * 
     * @return array
     */
    public static function allOptions() {
        $cfg = Config::get('transfer_options');
        if(!is_array($cfg)) $cfg = array();
        
        $options = array();
        foreach(TransferOptions::all() as $d => $name) {
            $option = array(
                'available' => false,
                'advanced' => false,
                'default' => false
            );
            
            if(array_key_exists($name, $cfg))
                foreach(array('available', 'advanced', 'default') as $p)
                    if(array_key_exists($p, $cfg[$name]))
                        $option[$p] = $cfg[$name][$p];
            
            $options[$name] = $option;
        }
        
        return $options;
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
     * Check if transfer has option
     * 
     * @param string $option
     * 
     * @return bool
     */
    public function hasOption($option) {
        return is_array($this->options) && in_array($option, $this->options);
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
            'id', 'status', 'user_id', 'user_email', 'guest_id',
            'subject', 'message', 'created', 'expires', 'options'
        ))) return $this->$property;
        
        if($property == 'user' || $property == 'owner') {
            return User::fromId($this->user_id);
        }
        
        if($property == 'guest') {
            return $this->guest_id ? Guest::fromId($this->guest_id) : null;
        }
        
        if($property == 'files') {
            if(is_null($this->filesCache)) $this->filesCache = File::fromTransfer($this);
            return $this->filesCache;
        }
        
        if($property == 'size') {
            return array_sum(array_map(function($file) {
                return $file->size;
            }, $this->files));
        }
        
        if($property == 'recipients') {
            if(is_null($this->recipientsCache)) $this->recipientsCache = Recipient::fromTransfer($this);
            return $this->recipientsCache;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     * 
     * @param string $property property to get
     * @param mixed $value value to set property to
     * 
     * @throws BadStatusException
     * @throws BadExpireException
     * @throws PropertyAccessException
     */
    public function __set($property, $value) {
        if($property == 'status') {
            $value = strtolower($value);
            if(!TransferStatuses::isValidValue($value)) throw new TransferBadStatusException($value);
            $this->status = (string)$value;
        }else if($property == 'user_email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->user_email = (string)$value;
        }else if($property == 'guest') {
            $gv = ($value instanceof Guest) ? $value : Guest::fromId($value);
            $this->guest_id = $gv->id;
        }else if($property == 'subject') {
            $this->subject = (string)$value;
        }else if($property == 'message') {
            $this->message = (string)$value;
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
        }else if($property == 'options') {
            $this->options = $value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Adds a file
     * 
     * @param string $name the file name
     * @param string $size the file size
     * 
     * @return File
     */
    public function addFile($name, $size, $mime_type = null) {
        // Create and save new recipient
        $file = File::create($this);
        $file->name = $name;
        $file->size = $size;
        $file->mime_type = $mime_type ? $mime_type : 'application/binary';
        $file->save();
        
        // Update local cache
        if(!is_null($this->filesCache)) $this->filesCache[$file->id] = $file;
        
        return $file;
    }
    
    /**
     * Removes a file
     * 
     * @param mixed $file File or file id
     */
    public function removeFile($file) {
        if(!is_object($file)) $file = File::fromId($file);
        
        // Delete
        $file->delete();
        
        // Update local cache
        if(!is_null($this->filesCache) && array_key_exists($file->id, $this->filesCache)) unset($this->filesCache[$file->id]);
    }
    
    /**
     * Adds a recipient
     * 
     * @param string $email email to add as recipient
     * 
     * @return Recipient
     */
    public function addRecipient($email) {
        // Create and save new recipient
        $recipient = Recipient::create($this, $email);
        $recipient->save();
        
        // Update local cache
        if(!is_null($this->recipientsCache)) $this->recipientsCache[$recipient->id] = $recipient;
        
        return $recipient;
    }
    
    /**
     * Removes a recipient
     * 
     * @param mixed $recipient Recipient or recipient id
     */
    public function removeRecipient($recipient) {
        if(!is_object($recipient)) $recipient = Recipient::fromId($recipient);
        
        // Delete
        $recipient->delete();
        
        // Update local cache
        if(!is_null($this->recipientsCache) && array_key_exists($recipient->id, $this->recipientsCache)) unset($this->recipientsCache[$recipient->id]);
    }
    
    /**
     * This function does stuffs when a transfer become available
     */
    public function makeAvailable(){
        if(!count($this->files))
            throw new TransferNoFilesException();
        
        if(!count($this->recipients))
            throw new TransferNoRecipientsException();
        
        $this->status = TransferStatuses::AVAILABLE;
        $this->save();
        
        Auth::user()->saveFrequentRecipients($this->recipients);
        
        $files = array();
        foreach($this->files as $file) {
            $files[] = $file->name.' ('.Utilities::formatBytes($file->size).')';
        }
        
        $ctn = Lang::translateEmail('transfer_available')->r($this, array(
            'text_file_list' => (count($files) > 1) ? '  - '.implode("\n  - ", $files) : $files[0],
            'html_file_list' => (count($files) > 1) ? '<ul><li>'.implode('</li><li>', $files).'</li></ul>' : $files[0],
        ));
        
        foreach($this->recipients as $recipient) {
            $mail = new ApplicationMail($ctn->r($recipient));
            $mail->send();
        }
    }
    
    /*
     * Save transfer then log
     */
    public function save() {
        parent::save();
        Logger::logActivity(LogEventTypes::TRANSFER_START, $this);
     }
     
}
