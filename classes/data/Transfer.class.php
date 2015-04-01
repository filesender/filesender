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
        'lang' => array(
            'type' => 'string',
            'size' => 8,
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
        'made_available' => array(
            'type' => 'datetime',
            'null' => true
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
    const UPLOADING = "status = 'uploading' ORDER BY created DESC";
    const AVAILABLE = "status = 'available' ORDER BY created DESC";
    const CLOSED = "status = 'closed' ORDER BY created DESC";
    const EXPIRED = "expires < :date ORDER BY expires ASC";
    const FAILED = "created < :date AND (status = 'created' OR status = 'started' OR status = 'uploading') ORDER BY expires ASC";
    const AUDITLOG_EXPIRED = "expires < :date ORDER BY expires ASC";
    const FROM_USER = "user_id = :user_id AND status='available' ORDER BY created DESC";
    const FROM_USER_CLOSED = "user_id = :user_id AND status='closed' ORDER BY created DESC";
    const FROM_GUEST = "guest_id = :guest_id AND status='available' ORDER BY created DESC";
    
    /**
     * Properties
     */
    protected $id = null;
    protected $status = null;
    protected $user_id = null;
    protected $user_email = null;
    protected $guest_id = null;
    protected $lang = null;
    protected $subject = null;
    protected $message = null;
    protected $created = 0;
    protected $made_available = 0;
    protected $expires = 0;
    protected $options = array();
    
    /**
     * Related objects cache
     */
    private $filesCache = null;
    private $recipientsCache = null;
    private $logsCache = null;
    
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
     * @param bool $closed
     * 
     * @return array of Transfer
     */
    public static function fromUser($user, $closed = false) {
        if($user instanceof User) $user = $user->id;
        
        return self::all($closed ? self::FROM_USER_CLOSED : self::FROM_USER, array(':user_id' => $user));
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
        
        if(!$user_email) $user_email = Auth::user()->email;
        
        if(Auth::isGuest()) {
            $transfer->guest = AuthGuest::getGuest();
            $user_email = $transfer->guest->email;
            
        } else if(!Auth::isRemote()) {
            if(!in_array($user_email, Auth::user()->email_addresses))
                throw new BadEmailException($user_email);
        }
        
        $transfer->__set('user_email', $user_email);
        
        $transfer->__set('expires', $expires);
        
        $transfer->created = time();
        $transfer->status = TransferStatuses::CREATED;
        
        $transfer->lang = Lang::getCode();
        
        return $transfer;
    }
    
    /**
     * Get default expire date
     * 
     * @return int timestamp
     */
    public static function getDefaultExpire() {
        
        $days = Config::get('default_transfer_days_valid');
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get max expire date
     * 
     * @return int timestamp
     */
    public static function getMaxExpire() {
        
        $days = Config::get('max_transfer_days_valid');
        
        if(!$days) $days = Config::get('default_daysvalid'); // @deprecated legacy
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get used/available volume from available transfers (without fetching it from storage)
     * 
     * @return array
     */
    public static function getUsage() {
        $quota = Config::get('host_quota');
        
        $used = 0;
        $s = DBI::query('SELECT size FROM '.File::getDBTable().' INNER JOIN '.self::getDBTable().' ON ('.self::getDBTable().'.id = '.File::getDBTable().'.transfer_id) WHERE status=\'available\'');
        foreach($s->fetchAll() as $r)
            $used += $r['size'];
        
        return array(
            'total' => $quota,
            'used' => $used,
            'available' => $quota ? max(0, $quota - $used) : null
        );
    }
    
    /**
     * Get uploading transfers
     * 
     * @return array of Transfer
     */
    public static function allUploading() {
        return self::all(self::UPLOADING);
    }
    
    /**
     * Get expired transfers
     * 
     * @return array of Transfer
     */
    public static function allExpired() {
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d')));
    }
    
    /**
     * Get failed transfers
     * 
     * @return array of Transfer
     */
    public static function allFailed() {
        $days = Config::get('failed_transfer_cleanup_days');
        if(!$days) return array();
        return self::all(self::FAILED, array(':date' => date('Y-m-d', time() - ($days * 24 * 3600))));
    }
    
    /**
     * Get expired transfers whose auditlogs expired
     * 
     * @return array of Transfer
     */
    public static function allExpiredAuditlogs() {
        $days = Config::get('auditlog_lifetime');
        if(is_null($days)) $days = 0;
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d', time() - ($days * 24 * 3600))));
    }
    
    /**
     * Delete the transfer related objects
     */
    public function beforeDelete() {
        AuditLog::clean($this);
        
        foreach($this->files as $file) $this->removeFile($file);
        
        foreach($this->recipients as $recipient) $this->removeRecipient($recipient);
        
        Logger::info('Transfer#'.$this->id.' deleted');
    }
    
    /**
     * Close the transfer
     */
    public function close($manualy = true) {
        switch($this->status) {
            case TransferStatuses::CREATED :
            case TransferStatuses::STARTED :
            case TransferStatuses::UPLOADING :
                // Transfer still not available, delete it
                $this->delete();
                return;
            
            case TransferStatuses::AVAILABLE :
                // Transfer available, proceed
                break;
            
            case TransferStatuses::CLOSED :
                // Transfer already closed, do nothing
                return;
        }
        
        // Close the transfer
        $this->status = TransferStatuses::CLOSED;
        if($manualy) $this->expires = time(); // Set expiration date so that auditlogs are cleaned the right way
        $this->save();
        
        // Log action
        Logger::logActivity($manualy ? LogEventTypes::TRANSFER_CLOSED : LogEventTypes::TRANSFER_EXPIRED, $this);
        
        if(!$this->hasOption(TransferOptions::GET_A_LINK)) {
            // Send notification to all recipients 
            foreach($this->recipients as $recipient)
                $this->sendToRecipient($manualy ? 'transfer_deleted' : 'transfer_expired', $recipient);
        }
        
        // Send notification to owner
        TranslatableEmail::quickSend($manualy ? 'transfer_deleted_receipt' : 'transfer_expired_receipt', $this->owner, $this);
        
        // Send report if needed
        if(!is_null(Config::get('auditlog_lifetime')) && $this->hasOption(TransferOptions::EMAIL_REPORT_ON_CLOSING)) {
            $report = new Report($this);
            $report->sendTo($this->owner);
        }
        
        if(!Config::get('auditlog_lifetime')) {
            // Delete all transfer data if auditlogs are not kept after transfer closing
            $this->delete();
        } else {
            // In case we keep audit data for some time only delete actual file data in storage
            foreach($this->files as $file)
                Storage::deleteFile($file);
        }
        
        Logger::info('Transfer#'.$this->id.' '.($manualy ? 'closed manually' : ' expired'));
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
        $options = Config::get('transfer_options');
        if(!is_array($options)) $options = array();
        
        foreach(TransferOptions::all() as $d => $name) {
            if(!array_key_exists($name, $options))
                $options[$name] = array(
                    'available' => false,
                    'advanced' => false,
                    'default' => false
                );
            
            foreach(array('available', 'advanced', 'default') as $p) {
                if(!array_key_exists($p, $options[$name]))
                    $options[$name][$p] = false;
                
                $options[$name][$p] = (bool)$options[$name][$p];
            }
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
     * Tells wether the transfer is expired
     * 
     * @return bool
     */
    public function isExpired() {
        $today = (24 * 3600) * floor(time() / (24 * 3600));
        return $this->expires < $today;
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
            'subject', 'message', 'created', 'made_available',
            'expires', 'options', 'lang'
        ))) return $this->$property;
        
        if($property == 'user' || $property == 'owner') {
            $user = User::fromId($this->user_id);
            $user->email_addresses = $this->user_email;
            return $user;
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
        
        if($property == 'first_recipient') {
            $recipients = array_values($this->recipients);
            return $recipients[0];
        }
        
        if($property == 'recipients_with_error') {
            return array_filter($this->recipients, function($recipient) {
                return count($recipient->errors);
            });
        }
        
        if($property == 'auditlogs') {
            if(is_null($this->logsCache)) $this->logsCache = AuditLog::fromTransfer($this);
            return $this->logsCache;
        }
        
        if($property == 'downloads') {
            return array_filter($this->auditlogs, function($log) {
                return $log->event == LogEventTypes::DOWNLOAD_ENDED;
            });
        }
        
        if($property == 'is_expired') return $this->isExpired();
        
        if($property == 'made_available_time') return $this->made_available ? ($this->made_available - $this->created) : null;
        
        if($property == 'upload_start') return min(array_map(function($file) {
            return $file->upload_start;
        }, $this->files));
        
        if($property == 'upload_end') return max(array_map(function($file) {
            return $file->upload_end;
        }, $this->files));
        
        if($property == 'upload_time') return $this->upload_end - $this->upload_start;
        
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
            if($value == TransferStatuses::AVAILABLE && !$this->made_available) $this->made_available = time();
            $this->status = (string)$value;
        }else if($property == 'user_email') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->user_email = (string)$value;
        }else if($property == 'guest') {
            $gv = ($value instanceof Guest) ? $value : Guest::fromId($value);
            $this->guest_id = $gv->id;
        }else if($property == 'lang') {
            if(!array_key_exists($value, Lang::getAvailableLanguages())) throw new BadLangCodeException($value);
            $this->lang = (string)$value;
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
            if($value < floor(time() / (24 * 3600)) || $value > self::getMaxExpire()) {
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
        // Check if already exists
        if(!is_null($this->filesCache)) {
            $matches = array_filter($this->filesCache, function($file) use($name, $size) {
                return ($file->name == $name) && ($file->size == $size);
            });
            
            if(count($matches)) return array_shift($matches);
        }
        
        // Create and save new recipient
        $file = File::create($this);
        $file->name = $name;
        $file->size = $size;
        $file->mime_type = $mime_type ? $mime_type : 'application/binary';
        $file->save();
        
        // Update local cache
        if(!is_null($this->filesCache)) $this->filesCache[$file->id] = $file;
        
        Logger::info('File#'.$file->id.' ('.$file->name.', '.$file->size.' bytes) added to Transfer#'.$this->id);
        
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
        
        Logger::info('File#'.$file->id.' ('.$file->name.', '.$file->size.' bytes) removed from Transfer#'.$this->id);
    }
    
    /**
     * Adds a recipient
     * 
     * @param string $email email to add as recipient
     * 
     * @return Recipient
     */
    public function addRecipient($email) {
        // Check if already exists
        if(!is_null($this->recipientsCache)) {
            $matches = array_filter($this->recipientsCache, function($recipient) use($email) {
                return $recipient->email == $email;
            });
            
            if(count($matches)) return array_shift($matches);
        }
        
        // Create and save new recipient
        $recipient = Recipient::create($this, $email);
        $recipient->save();
        
        // Update local cache
        if(!is_null($this->recipientsCache)) $this->recipientsCache[$recipient->id] = $recipient;
        
        Logger::info('Recipient#'.$recipient->id.' ('.$recipient->email.') added to Transfer#'.$this->id);
        
        return $recipient;
    }
    
    /**
     * Test if some email address is in the recipient list
     * 
     * @param string $email
     * 
     * @return bool
     */
    public function isRecipient($email) {
        foreach($this->recipients as $recipient)
            if($recipient->email == $email)
                return true;
        
        return false;
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
        
        Logger::info('Recipient#'.$recipient->id.' ('.$recipient->email.') removed from Transfer#'.$this->id);
    }
    
    /**
     * This function does stuffs when a transfer become available
     */
    public function makeAvailable() {
        Logger::logActivity(LogEventTypes::UPLOAD_ENDED, $this);
        
        if(!count($this->files))
            throw new TransferNoFilesException();
        
        if(!count($this->recipients))
            throw new TransferNoRecipientsException();
        
        $this->status = TransferStatuses::AVAILABLE;
        $this->made_available = time();
        $this->save();
        Logger::logActivity(LogEventTypes::TRANSFER_AVAILABLE, $this);
        
        if(Auth::isGuest()) {
            $guest = AuthGuest::getGuest();
            
            $guest->transfer_count++;
            
            if($this->hasOption(TransferOptions::EMAIL_UPLOAD_COMPLETE))
                TranslatableEmail::quickSend('guest_upload_complete', $guest->owner, $guest);
            
            if($guest->hasOption(GuestOptions::VALID_ONLY_ONE_TIME))
                $guest->status = GuestStatuses::CLOSED;
            
            $guest->save();
        } else {
            if($this->hasOption(TransferOptions::EMAIL_UPLOAD_COMPLETE))
                TranslatableEmail::quickSend('upload_complete', $this->owner, $this);
            
            Auth::user()->saveFrequentRecipients($this->recipients);
        }
        
        
        if(!$this->hasOption(TransferOptions::GET_A_LINK)) {
            if($this->hasOption(TransferOptions::ADD_ME_TO_RECIPIENTS) && !$this->isRecipient($this->user_email))
                $this->addRecipient($this->user_email);
            
            foreach($this->recipients as $recipient) {
                $this->sendToRecipient('transfer_available', $recipient);
            }
            
            Logger::logActivity(LogEventTypes::TRANSFER_SENT, $this, Auth::isGuest() ? AuthGuest::getGuest() : null);
        }
        
        Logger::info('Transfer#'.$this->id.' made available'.(Auth::isGuest() ? ' by guest: '.AuthGuest::getGuest()->email : '').', took '.$this->made_available_time.'s');
    }
    
    /**
     * Send reminder to recipients
     */
    public function remind() {
        if($this->hasOption(TransferOptions::GET_A_LINK)) return;
        
        foreach($this->recipients as $recipient)
            $this->sendToRecipient('transfer_reminder', $recipient);
        
        Logger::info('Transfer#'.$this->id.' reminded to recipients');
    }
    
    /*
     * Start transfer and log
     */
    public function start() {
        $this->status = TransferStatuses::STARTED;
        $this->save();
        
        if (Auth::isGuest()){
            $guest = AuthGuest::getGuest();
            
            if($guest->hasOption(GuestOptions::EMAIL_UPLOAD_STARTED))
                TranslatableEmail::quickSend('guest_upload_start', $guest->owner, $guest);
        }
        
        Logger::logActivity(LogEventTypes::TRANSFER_STARTED, $this,Auth::isGuest()?AuthGuest::getGuest():null);
        Logger::info('Transfer#'.$this->id.' started'.(Auth::isGuest() ? ' by guest: '.AuthGuest::getGuest()->email : ''));
    }
    
    /**
     * Set uploading and log
     */
    public function isUploading() {
        if($this->status != TransferStatuses::STARTED) return;
        
        $this->status = TransferStatuses::UPLOADING;
        $this->save();
        Logger::logActivity(LogEventTypes::UPLOAD_STARTED, $this);
        Logger::info('Transfer#'.$this->id.' upload started');
    }
    
    /**
     * Send message to recipient, handling options
     * 
     * @param string $translation_id lang string id
     * @param Recipient $recipient
     * @param mixed ... translation variables
     */
    public function sendToRecipient($translation_id, $recipient) {
        $args = func_get_args();
        $args[] = $this;
        
        $mail = call_user_func_array('TranslatableEmail::prepare', $args);
        
        if($this->hasOption(TransferOptions::EMAIL_ME_COPIES))
            $mail->bcc($this->user_email);
        
        $mail->send();
    }
}
