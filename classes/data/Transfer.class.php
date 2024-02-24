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
 * Represents a transfer in database
 *
 * @property array $filesCache related filesCache
 * @property array $collectionsCache related collectionsCache
 * @property array $recipientsCache related recipientsCache
 */
class Transfer extends DBObject
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
        'guest_id' => array(
            'type' => 'uint',
            'size' => 'big',
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
        'expiry_extensions' => array(
            'type' => 'uint',
            'size' => 'small',
            'default' => 0
        ),
        'status' => array(
            'type' => 'string',
            'size' => 32
        ),
        'options' => array(
            'type' => 'text',
            'transform' => 'json'
        ),
        'key_version' => array(
            'type'    => 'uint',
            'size'    => 'small',
            'null'    => false,
            'default' => 0
        ),
        'salt' => array(
            'type'    => 'string',
            'size'    => '32',
            'null'    => true,
        ),
        'password_version' => array(
            'type'    => 'uint',
            'size'    => 'small',
            'null'    => false,
            'default' => 1
        ),
        'password_encoding' => array(
            'type'    => 'uint',
            'size'    => 'medium',
            'null'    => false,
            'default' => 0
        ),
        'password_hash_iterations' => array(
            'type'    => 'uint',
            'size'    => 'big',
            'null'    => false,
            'default' => 150000
        ),
        // This is some entropy from the uploading client
        // A single pool of entropy is used here to allow
        // different code paths to get some material without needing
        // specific fields for each such use.
        //
        // See decodeClientEntropy() in crypto_app for how to use this
        // client side.
        'client_entropy' => array(   
            'type'    => 'string',
            'size'    => '44',
            'null'    => true,
        ),

        //
        // This is to hand to a client that is making the transfer and to upload
        // they have to hand it back to ensure they are the one who made the transfer
        //
        'roundtriptoken' => array(
            'type'    => 'string',
            'size'    => '44',
            'null'    => true,
        ),

        'guest_transfer_shown_to_user_who_invited_guest' => array(
            'type'    => 'bool',
            'null'    => true,
            'default' => true,
        ),

        'storage_filesystem_per_day_buckets' => array(
            'type'    => 'bool',
            'null'    => false,
            'default' => false,
        ),
        'storage_filesystem_per_hour_buckets' => array(
            'type'    => 'bool',
            'null'    => false,
            'default' => false,
        ),

        
    );

    /**
     * This is the SQL view definition of the main view for transfers.
     * This method is here to allow File to use this view in it's own view
     * without needing an explicit ordering between classes during database upgrades.
     */
    public static function getPrimaryViewDefinition($dbtype)
    {
        return 'select *'
             . DBView::columnDefinition_age($dbtype, 'created')
             . DBView::columnDefinition_age($dbtype, 'expires')
             . DBView::columnDefinition_age($dbtype, 'made_available')
             . DBView::columnDefinition_is_encrypted('options', 'is_encrypted')
             . " , (CASE WHEN password_version=1 THEN 'user' ELSE 'generated' END) as password_origin "
             . '  from ' . self::getDBTable();
    }
    public static function getViewMap()
    {
        $a = array();
        $authviewdef = array();
        $sizeviewdev = array();
        $recipientviewdev = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = self::getPrimaryViewDefinition($dbtype);
            
            $authviewdef[$dbtype] = 'select t.id as id,t.userid as userid,u.authid as authid,a.saml_user_identification_uid as user_id,'
                                  . 't.made_available,t.expires,t.created '
                                  . ' FROM '
                                      . self::getDBTable().' t, '
                                            . call_user_func('User::getDBTable').' u, '
                                            . call_user_func('Authentication::getDBTable').' a where t.userid = u.id and u.authid = a.id ';

            $sizeviewdev[$dbtype] = 'select t.*,sum(f.size) as size from '
                                  . self::getDBTable().' t, '
                                        . call_user_func('File::getDBTable').' f '
                                        . ' where '
                                        . ' f.transfer_id=t.id '
                                        . '  group by t.id ';
            
            $recipientviewdev[$dbtype] = 'select t.*,r.email as recipientemail,r.id as recipientid from '
                                       . self::getDBTable().' t, '
                                             . call_user_func('Recipient::getDBTable').' r '
                                             . ' where '
                                             . ' r.transfer_id=t.id ';
            $filesview[$dbtype] = 'select t.*,f.name as filename,f.size as filesize from '
                                . self::getDBTable().' t, '
                                      . call_user_func('File::getDBTable').' f '
                                      . ' where '
                                      . ' f.transfer_id=t.id ';

            $auditlogsview[$dbtype] = 'select t.*,0 as fileid,a.created as acreated,a.author_type,a.author_id,a.target_type,a.target_id,a.event,a.id as aid '
                                    . ' from '
                                    . self::getDBTable().' t, '
                                          . call_user_func('AuditLog::getDBTable').' a '
                                          . " where "
                                          . " a.target_id=" . DBLayer::toViewVarCharCast("t.id",255)
                                          . " and target_type = 'Transfer'  "
                                     . " UNION "
                                          . 'select t.*,0 as fileid,a.created as acreated,a.author_type,a.author_id,a.target_type,a.target_id,a.event,a.id as aid '
                                          . ' from '
                                          . self::getDBTable().' t, '
                                          . call_user_func('AuditLog::getDBTable').' a, '
                                          . call_user_func('File::getDBTable').' f '
                                          . " where  f.transfer_id=t.id  "
                                          . "   and a.target_id=" .  DBLayer::toViewVarCharCast("f.id",255)
                                                                            . "   and target_type = 'File'  ";
            
            $auditlogsviewdlcss[$dbtype] = 'select id,count(*) as count from transfersauditlogsview where  '
                                             . " ( event = 'download_ended' or event = 'archive_download_ended' ) group by id ";
                
            $auditlogsviewdlc[$dbtype] = 'select t.*,count from '
                                       . self::getDBTable() . ' t '
                                             . " left outer join transfersauditlogsdlsubselectcountview zz "
                                             . " on t.id = zz.id  " ;
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a
                    , 'transfersauthview' => $authviewdef
                    , 'transferssizeview' => $sizeviewdev
                    , 'transfersrecipientview' => $recipientviewdev
                    , 'transfersfilesview' => $filesview
                    , 'transfersauditlogsview' => $auditlogsview
                    , 'transfersauditlogsdlsubselectcountview' => $auditlogsviewdlcss
                    , 'transfersauditlogsdlcountview' => $auditlogsviewdlc
        );
    }

    protected static $secondaryIndexMap = array(
        'userid' => array(
            'userid' => array()
        ),
        'user_email' => array(
            'user_email' => array()
        ),
        'expires' => array(
            'expires' => array()
        )
    );

    /**
     * Config variables
     */
    const OBJECT_EXPIRY_DATE_EXTENSION_CONFIGKEY = "allow_transfer_expiry_date_extension";
    
    /**
     * Set selectors
     */
    const UPLOADING = "status = 'uploading' ORDER BY created DESC";
    const AVAILABLE = "status = 'available' ORDER BY created DESC";
    const CLOSED = "status = 'closed' ORDER BY created DESC";
    const EXPIRED = "expires <= :date ORDER BY expires ASC";
    const FAILED = "created < :date AND (status = 'created' OR status = 'started' OR status = 'uploading') ORDER BY expires ASC";
    const AUDITLOG_EXPIRED = "expires < :date ORDER BY expires ASC";
    const FROM_USER = "userid = :userid AND status='available' ORDER BY created DESC";
    const FROM_USER_CLOSED = "userid = :userid AND status='closed' ORDER BY created DESC";
    const FROM_GUEST = "guest_id = :guest_id AND status='available' ORDER BY created DESC";
    const COUNT_UPLOADED_FROM_GUEST = "guest_id = :guest_id AND status!='created' ";
    const UPLOADING_NO_ORDER = "status = 'uploading' ";
    const AVAILABLE_NO_ORDER = "status = 'available' ";
    const CLOSED_NO_ORDER = "status = 'closed' ";
    const FROM_USER_NO_ORDER        = "userid = :userid AND status='available' and ( guest_id is null or guest_transfer_shown_to_user_who_invited_guest ) ";
    const FROM_USER_CLOSED_NO_ORDER = "userid = :userid AND status='closed'    and ( guest_id is null or guest_transfer_shown_to_user_who_invited_guest ) ";

    const ROUNDTRIPTOKEN_ENTROPY_BYTE_COUNT = 16;
    
    /**
     * Properties
     */
    protected $id = null;
    protected $userid = null;
    protected $status = null;
    protected $user_id = null;
    protected $user_email = null;
    protected $guest_id = null;
    protected $lang = null;
    protected $subject = null;
    protected $message = null;
    protected $created = 0;
    protected $made_available = null;
    protected $expires = 0;
    protected $expiry_extensions = 0;
    protected $options = array();
    protected $key_version = 0;
    protected $salt = '';
    protected $password_version = 1;
    protected $password_encoding = 0;
    protected $password_encoding_string = 'none';
    protected $password_hash_iterations = 150000;
    protected $client_entropy = '';
    protected $roundtriptoken = '';
    protected $guest_transfer_shown_to_user_who_invited_guest = true;
    protected $storage_filesystem_per_day_buckets = false;
    protected $storage_filesystem_per_hour_buckets = false;

    
    /**
     * Related objects cache
     */
    private $filesCache = null;
    private $collectionsCache = null;
    private $recipientsCache = null;
    private $logsCache = null;
    private static $optionsCache = null;

    /**
     * Allows a $force param to be sent to beforeDelete() to ignore
     * errors deleting individual files and continue
     */
    public $deleteForce = false;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of transfer to load from database (null if loading not wanted)
     * @param array $data data to create the transfer from (if already fetched from database)
     *
     * @throws TransferNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        $this->storage_filesystem_per_day_buckets = Config::get('storage_filesystem_per_day_buckets');
        $this->storage_filesystem_per_hour_buckets = Config::get('storage_filesystem_per_hour_buckets');
        
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new TransferNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }

        // Load collections if they exist
        if ($this->getOption(TransferOptions::COLLECTION)) {
            CollectionType::initialize();
            $this->collectionsCache = Collection::fromTransfer($this);
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

        $this->password_encoding_string = DBConstantPasswordEncoding::reverseLookup($this->password_encoding);
        
    }
    
    /**
     * Get transfers from user
     *
     * @param mixed $user User or user id
     * @param bool $closed
     *
     * @return array of Transfer
     */
    public static function fromUser($user, $closed = false, $limit = null, $offset = null)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        return self::all(
            array('where' => $closed ? self::FROM_USER_CLOSED : self::FROM_USER
                               ,'limit' => $limit
                               ,'offset' => $offset
                               ),
            array(':userid' => $user)
                         );
    }
    /**
     * Get transfers from user. This is like fromUser() but allows you to pass in
     * data from a TransferQueryOrder or other source to pick data from a view and 
     * sort the result explicitly. A view can be used to pick the resulting data from one
     * of the transfers views defined above, for example, using transfersfilesview to
     * include the total transfer size that can then be used to "ORDER BY size".
     *
     * $limit and $offset can be used to page through an ordered result set by starting
     * at $offset in the results and only returning $limit results. The next page would
     * then be $newoffset = $offset+$limit and a slice of $limit results returned again.
     *
     * @param mixed $user User or user id
     * @param string $viewClause the view to use for example from TransferQueryOrder::getViewName()
     * @param string $orderByClause how to ORDER BY the results. For example from TransferQueryOrder::getOrderByClause()
     * @param bool $closed
     * @param int $limit how many result to return
     * @param int $offset where to start results from in ordered result set.
     *
     * @return array of Transfer
     */
    public static function fromUserOrdered( $user,
                                            $viewClause,
                                            $orderByClause,
                                            $closed = false, $limit = null, $offset = null)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        return self::all(
            array(
                'view'  => $viewClause,
                'order' => $orderByClause,
                'where' => $closed ? self::FROM_USER_CLOSED_NO_ORDER : self::FROM_USER_NO_ORDER
                               ,'limit' => $limit
                               ,'offset' => $offset
                               ),
            array(':userid' => $user)
                         );
    }
    
    /**
     * Get transfers from guest
     *
     * @param mixed $guest Guest or Guest id
     *
     * @return array of Transfer
     */
    public static function fromGuest($guest)
    {
        if ($guest instanceof Guest) {
            $guest = $guest->id;
        }
        
        return self::all(self::FROM_GUEST, array(':guest_id' => $guest));
    }


    /**
     * Get number of transfers created by guest that were at some stage
     * made available.
     *
     * @param mixed $guest Guest or Guest id
     *
     * @return int count
     */
    public static function countUploadedFromGuest($guest)
    {
        if ($guest instanceof Guest) {
            $guest = $guest->id;
        }
        
        return self::count(self::COUNT_UPLOADED_FROM_GUEST, array(':guest_id' => $guest));
    }
    

    /**
     * Get transfers from guests of user
     *
     * @param mixed $user User or user id
     * @param $user_can... when true results only contain transfers that the guest has shared with the user.
     *   for example, by having the GuestOptions::CAN_ONLY_SEND_TO_ME option set.
     *
     * @return array of Transfer
     */
    public static function fromGuestsOf($user,$user_can_only_view_guest_transfers_shared_with_them)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }


        // find the transfers for all the user's guests
        $sql = "select t.id as tid from "
             . Transfer::getDBTable() . " t, "
             . Guest::getDBTable()    . " g "
             . " where "
             . " g.userid   = :userid AND (g.expires is null or g.expires > :date) AND "
             . " t.guest_id = g.id    AND t.status  = 'available' ";
        if( $user_can_only_view_guest_transfers_shared_with_them ) {
            $sql .= " and t.guest_transfer_shown_to_user_who_invited_guest ";
        }
        $sql .= " order by t.created desc ";
        $statement = DBI::prepare($sql);
        $placeholders =  array(':userid' => $user, ':date' => date('Y-m-d'));
        $statement->execute($placeholders);
        $records = $statement->fetchAll();
        $transfers = array();
        foreach ($records as $r) {
            array_push( $transfers, Transfer::fromID($r['tid']));
        }
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
    public static function create($expires, $user_email = null)
    {
        $transfer = new self();
        
        // Init caches to empty to avoid db queries
        $transfer->recipientsCache = array();
        $transfer->logsCache = array();
        
        $transfer->userid = Auth::user()->id;

        $transfer->roundtriptoken = Utilities::generateEntropyString(
            self::ROUNDTRIPTOKEN_ENTROPY_BYTE_COUNT );

        if (!$user_email) {
            $user_email = Auth::user()->email;
        }
        
        if (Auth::isGuest()) {
            // If guest keep guest's owner identity
            $transfer->guest = AuthGuest::getGuest();
            $user_email = $transfer->guest->email;
        } elseif (!Auth::isRemote()) {
            // Check that sender address is in the user's addresses unless remote
            if (!in_array($user_email, Auth::user()->email_addresses)) {
                throw new BadEmailException($user_email);
            }
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
    public static function getDefaultExpire()
    {
        $days = Config::get('default_transfer_days_valid');

        if( Auth::isGuest()) {
            $guest = AuthGuest::getGuest();
            if( $guest->guest_upload_default_expire_is_guest_expire ) {
                $days = min( Config::get('max_transfer_days_valid'),
                             $guest->expires_in_days );
            }
        }
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get max expire date
     *
     * @return int timestamp
     */
    public static function getMaxExpire()
    {
        $days = Config::get('max_transfer_days_valid');
        
        if (!$days) {
            $days = Config::get('default_daysvalid');
        } // @deprecated legacy
        
        return strtotime('+'.$days.' day');
    }
    
    /**
     * Get used/available volume from available transfers (without fetching it from storage)
     *
     * @return array
     */
    public static function getUsage()
    {
        $quota = Config::get('host_quota');
        
        $used = 0;
        $s = DBI::query('SELECT SUM(size) AS size FROM '.File::getDBTable().' INNER JOIN '.self::getDBTable().' ON ('.self::getDBTable().'.id = '.File::getDBTable().'.transfer_id) WHERE status=\'available\'');
        foreach ($s->fetchAll() as $r) {
            $used += $r['size'];
        }
        
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
    public static function allUploading()
    {
        return self::all(self::UPLOADING);
    }
    
    /**
     * Get expired transfers
     *
     * @return array of Transfer
     */
    public static function allExpired()
    {
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d')));
    }
    
    /**
     * Get failed transfers
     *
     * @return array of Transfer
     */
    public static function allFailed()
    {
        $days = Config::get('failed_transfer_cleanup_days');
        if (!$days) {
            return array();
        }
        return self::all(self::FAILED, array(':date' => date('Y-m-d', time() - ($days * 24 * 3600))));
    }
    
    /**
     * Get expired transfers whose auditlogs expired
     *
     * @return array of Transfer
     */
    public static function allExpiredAuditlogs()
    {
        $days = Config::get('auditlog_lifetime');
        if (is_null($days)) {
            $days = 0;
        }
        return self::all(self::EXPIRED, array(':date' => date('Y-m-d', time() - ($days * 24 * 3600))));
    }
    
    /**
     * Delete the transfer related objects
     */
    public function beforeDelete()
    {
        AuditLog::clean($this);
        
        if (!is_null($this->collections)) {
            foreach ($this->collections as $collection_type_id => $collectionList) {
                foreach ($collectionList as $collection) {
                    $this->removeCollection($collection);
                }
            }
        }
        
        foreach ($this->files as $file) {
            try {
                $this->removeFile($file);
            } catch (Exception $e) {
                if( $this->deleteForce ) {
                    Logger::warn("Transfer::delete() Failed to delete file error:" . $e->getMessage());
                } else {
                    throw $e;
                }
            }
        }
        
        foreach ($this->recipients as $recipient) {
            $this->removeRecipient($recipient);
        }
        
        foreach (TranslatableEmail::fromContext($this) as $translatable_email) {
            $translatable_email->delete();
        }
        
        Logger::info($this.' deleted');
    }

    public function userCanSeeTransfer()
    {
        if( !$this->guest_id )
            return true;
        if( $this->guest_transfer_shown_to_user_who_invited_guest ) {
            return true;
        }
        return false;
    }
    
    /**
     * Close the transfer
     */
    public function close( $manualy = true, $force = false )
    {
        $this->deleteForce = $force;
        
        switch ($this->status) {
            case TransferStatuses::CREATED:
            case TransferStatuses::STARTED:
            case TransferStatuses::UPLOADING:
                // Transfer still not available, delete it
                $this->delete();
                return;
            
            case TransferStatuses::AVAILABLE:
                // Transfer available, proceed
                break;
            
            case TransferStatuses::CLOSED:
                // Transfer already closed, do nothing
                return;
        }
        
        // Close the transfer
        $this->status = TransferStatuses::CLOSED;
        if ($manualy) {
            $this->expires = time();
        } // Set expiration date so that auditlogs are cleaned the right way
        $this->save();
        
        // Log action
        Logger::logActivity($manualy ? LogEventTypes::TRANSFER_CLOSED : LogEventTypes::TRANSFER_EXPIRED, $this);

        if (!$this->getOption(TransferOptions::GET_A_LINK)) {

            $email_message_type = 'transfer_expired';
            if( $manualy ) {
                $email_message_type = 'transfer_deleted';
            }
            
            // always email deleted transfers
            //     or optionally notify when a transfer has expired.
            if( $manualy || $this->getOption(TransferOptions::EMAIL_RECIPIENT_WHEN_TRANSFER_EXPIRES)) {
                // Send notification to all recipients
                foreach ($this->recipients as $recipient) {
                    $this->sendToRecipient( $email_message_type, $recipient );
                }
            }
        }
        
        // Send notification to owner
        if( $this->userCanSeeTransfer() ) {
            if ($this->getOption(TransferOptions::EMAIL_ME_ON_EXPIRE)) {
                TranslatableEmail::quickSend($manualy ? 'transfer_deleted_receipt' : 'transfer_expired_receipt', $this->owner, $this);
            }
        }
      
        // Send report if needed
        try {
            if (!is_null(Config::get('auditlog_lifetime')) && $this->getOption(TransferOptions::EMAIL_REPORT_ON_CLOSING)) {
                $report = new Report($this);
                $report->sendTo($this->owner);
            }
        } catch (Exception $e) {
            if( $force ) {
                Logger::warn("Failed to send report during transfer close. error:" . $e->getMessage());
            } else {
                throw $e;
            }
        }
        
        if (!Config::get('auditlog_lifetime')) {
            // Delete all transfer data if auditlogs are not kept after transfer closing
            $this->delete();
        } else {
            // In case we keep audit data for some time only delete actual file data in storage
            foreach ($this->files as $file) {
                try {
		    Logger::debug('Attempt to call Storage::deleteFile for ' . $file);
                    Storage::deleteFile($file);
                } catch (Exception $e) {
                    if( $force ) {
                        Logger::warn("Transfer::delete() Failed to delete file error:" . $e->getMessage());
                    } else {
                        throw $e;
                    }
                }
            }
        }
        
        Logger::info($this.' '.($manualy ? 'closed manually' : 'expired'));
    }
    
    /**
     * Check if user owns current transfer
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
     * Check that the user has read/write permission 
     * for this transfer.
     *
     * If the user is a guest then a valid 'vid' must be provided.
     * 
     * @return true if they are allowed or false if access should be forbidden
     */
    public function havePermission()
    {
        $user = Auth::user();

        // This should never happen
        if (Auth::isGuest() && Auth::isAdmin()) {
            return FALSE;
        }
        
        if (Auth::isGuest()) {
            // this will throw if there is no vid
            $guest = AuthGuest::getGuest();
            if( !$guest ) {
                return FALSE;
            }
            if( $guest->id != $this->guest_id ) {
                return FALSE;
            }
        }
        
        if (!$this->isOwner($user)) {
            if( !Auth::isAdmin()) {
                return FALSE;
            }
        }

        return TRUE;
    }
    
    /**
     * Get all options
     *
     * @return array
     */
    public static function allOptions()
    {
        if (is_null(self::$optionsCache)) {
            $options = Config::get('transfer_options');
            if (!is_array($options)) {
                $options = array();
            }
            
            foreach (TransferOptions::all() as $d => $name) {
                if (!array_key_exists($name, $options)) {
                    $options[$name] = array(
                        'available' => false,
                        'advanced' => false,
                        'default' => false
                    );
                }

                // default is false if not specified
                foreach (array('available', 'advanced', 'default') as $p) {
                    if (!array_key_exists($p, $options[$name])) {
                        $options[$name][$p] = false;
                    }
                }
            }
            
            self::$optionsCache = $options;
        }
        
        return self::$optionsCache;
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
     * Get specific available option
     *
     * @param string $name option name
     *
     * @return mixed
     */
    public static function availableOption($name)
    {
        $options = self::allOptions();
        
        return array_key_exists($name, $options) ? $options[$name] : null;
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
    public function setOption($option,$v)
    {
        // allow population from default.
        $this->getOption($option);
        
        $options = static::allOptions();
        if (array_key_exists($option, $options)) {
            $this->options[$option] = $v;
        }
        return $v;
    }
    
    /**
     * Tells wether the transfer is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        $today = (24 * 3600) * floor(time() / (24 * 3600));
        return $this->expires < $today;
    }
    
    /**
     * Validate and format options
     *
     * @param mixed $raw_options
     *
     * @return array
     */
    public static function validateOptions($raw_options)
    {
        $options = array();
        foreach ((array)$raw_options as $name => $value) {
            if (!TransferOptions::isValidValue($name)) {
                throw new BadOptionNameException($name);
            }
            
            if ($name == TransferOptions::REDIRECT_URL_ON_COMPLETE) {
                if ($value) {
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new BadURLException($value);
                    }
                } else {
                    $value = null;
                }
            } elseif ($name == TransferOptions::STORAGE_CLOUD_S3_BUCKET) {
                // no validation as this is only set server side.
            } else {
                $value = (bool)$value;
            }
            
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
            'id','status', 'user_id', 'user_email', 'guest_id',
            'subject', 'message', 'created', 'made_available',
            'expires', 'expiry_extensions', 'options', 'lang', 'key_version', 'userid',
            'password_version', 'password_encoding', 'password_encoding_string', 'password_hash_iterations'
            , 'client_entropy', 'roundtriptoken', 'guest_transfer_shown_to_user_who_invited_guest'
            , 'storage_filesystem_per_day_buckets', 'storage_filesystem_per_hour_buckets'
            
        ))) {
            return $this->$property;
        }

        if ($property == 'user' || $property == 'owner') {
            $user = User::fromID($this->userid);
            $user->email_addresses = $this->user_email;
            return $user;
        }
        
        if ($property == 'guest') {
            return $this->guest_id ? Guest::fromId($this->guest_id) : null;
        }
        
        if ($property == 'files') {
            if (is_null($this->filesCache)) {
                $this->filesCache = File::fromTransfer($this);
            }
            return $this->filesCache;
        }

        if ($property == 'collections') {
            return $this->collectionsCache;
        }

        if ($property == 'is_encrypted') {
            if (!array_key_exists('encryption', $this->options)) {
                return false;
            }
            return $this->options['encryption'];
        }
        if ($property == "get_a_link") {
            return $this->getOption(TransferOptions::GET_A_LINK);
        }
        if ($property == "must_be_logged_in_to_download") {
            return $this->getOption(TransferOptions::MUST_BE_LOGGED_IN_TO_DOWNLOAD);
        }
        
        if ($property == 'size') {
            return array_sum(array_map(function ($file) {
                return $file->size;
            }, $this->files));
        }
        
        if ($property == 'recipients') {
            if (is_null($this->recipientsCache)) {
                $this->recipientsCache = Recipient::fromTransfer($this);
            }
            return $this->recipientsCache;
        }
        
        if ($property == 'first_recipient') {
            $recipients = array_values($this->recipients);
            return $recipients[0];
        }
        
        if ($property == 'recipients_with_error') {
            return array_filter($this->recipients, function ($recipient) {
                return count($recipient->errors);
            });
        }
        
        if ($property == 'auditlogs') {
            if (is_null($this->logsCache)) {
                $this->logsCache = AuditLog::fromTransfer($this);
            }
            return $this->logsCache;
        }
        
        if ($property == 'downloads') {
            return array_filter($this->auditlogs, function ($log) {
                return in_array($log->event, array(LogEventTypes::DOWNLOAD_ENDED, LogEventTypes::ARCHIVE_DOWNLOAD_ENDED));
            });
        }
        
        if ($property == 'is_expired') {
            return $this->isExpired();
        }
        
        if ($property == 'expiry_date_extension') {
            return $this->getObjectExpiryDateExtension(false);
        } // No throw
        
        if ($property == 'made_available_time') {
            return $this->made_available ? ($this->made_available - $this->created) : null;
        }
        
        if ($property == 'upload_start') {
            return min(array_map(function ($file) {
                return $file->upload_start;
            }, $this->files));
        }
        
        if ($property == 'upload_end') {
            return max(array_map(function ($file) {
                return $file->upload_end;
            }, $this->files));
        }
        
        if ($property == 'upload_time') {
            if( empty($this->files)) {
                return 0;
            }
            return $this->upload_end - $this->upload_start;
        }

        if ($property == 'days_to_expire') {
            $now = time();
            $datediff = $this->expires - $now;
            $days_to_expire = round($datediff / (60 * 60 * 24));
            return $days_to_expire;
        }
        
        if ($property == 'link') {
            $tr_url = Utilities::http_build_query(array('s' => 'transfers#transfer_'.$this->id));
            // Utilities::http_build_query() has URL encoded $tr_url 
            // AuthSP::logonURL() will URL encode what is given to it,
            // so we must decode the string first to avoid a double encoding.
            $tr_url = urldecode( $tr_url );
            $auth_url = AuthSP::logonURL($tr_url);
            
            if (!preg_match('`^https?://[^/]+`', $auth_url)) {
                $base = Config::get('site_url');
                
                if (substr($auth_url, 0, 1) == '/') { // Absolute url
                    $base = preg_replace('`^(https?://[^/]+).*$`', '$1', $base);
                }
                
                $auth_url = $base.$auth_url;
            }
            
            return $auth_url;
        }
        if ($property == 'download_link') {
            $recipients = array_values($this->recipients);
            return $recipients[0]->download_link;
        }
        if ($property == 'salt') {
            if (strlen($this->salt)) {
                return $this->salt;
            }
            $this->salt = Crypto::generateSaltString(32);
            $this->save();
            return $this->salt;
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
    public function __set($property, $value)
    {
        if ($property == 'status') {
            $value = strtolower($value);
            if (!TransferStatuses::isValidValue($value)) {
                throw new TransferBadStatusException($value);
            }
            if ($value == TransferStatuses::AVAILABLE && !$this->made_available) {
                $this->made_available = time();
            }
            $this->status = (string)$value;
        } elseif ($property == 'user_email') {
            if (!Utilities::validateEmail($value)) {
                throw new BadEmailException($value);
            }
            $this->user_email = (string)$value;
        } elseif ($property == 'guest') {
            $gv = ($value instanceof Guest) ? $value : Guest::fromId($value);
            $this->guest_id = $gv->id;
        } elseif ($property == 'lang') {
            if (!array_key_exists($value, Lang::getAvailableLanguages())) {
                throw new BadLangCodeException($value);
            }
            $this->lang = (string)$value;
        } elseif ($property == 'subject') {
            $this->subject = (string)$value;
        } elseif ($property == 'message') {
            $this->message = (string)$value;
        } elseif ($property == 'expires') {
            if (preg_match('`^[0-9]{4}-[0-9]{2}-[0-9]{2}$`', $value)) {
                $value = strtotime($value);
            }
            
            if (!preg_match('`^[0-9]+$`', $value)) {
                throw new BadExpireException($value);
            }
            
            $value = (int)$value;
            if ($value < floor(time() / (24 * 3600)) || $value > self::getMaxExpire()) {
                throw new BadExpireException($value);
            }
            $this->expires = (string)$value;
        } elseif ($property == 'options') {
            $this->options = self::validateOptions($value);
        } elseif ($property == 'key_version') {
            $this->key_version = $value;
        } elseif ($property == 'password_version') {
            $this->password_version = $value;
        } elseif ($property == 'password_encoding') {
            DBConstantPasswordEncoding::validateCGIParamOrDIE($value);
            $this->password_encoding = DBConstantPasswordEncoding::lookup($value);
            $this->password_encoding_string = $value;
        } elseif ($property == 'password_hash_iterations') {
            $this->password_hash_iterations = $value;
        } elseif ($property == 'client_entropy') {
            $this->client_entropy = $value;
        } elseif ($property == 'guest_transfer_shown_to_user_who_invited_guest') {
            $this->guest_transfer_shown_to_user_who_invited_guest = $value;
        } elseif ($property == 'storage_filesystem_per_day_buckets') {
            $this->storage_filesystem_per_day_buckets = $value;
        } elseif ($property == 'storage_filesystem_per_hour_buckets') {
            $this->storage_filesystem_per_hour_buckets = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }

    /**
     * Adds a file
     *
     * @param string $path the file name
     * @param string $size the file size
     * @param string $mime_type the optional file mime_type
     * @param string $iv base64 encoded IV used to encrypt file 
     *
     * @return File
     */
    public function addFile($path, $size, $mime_type = null, $iv = null, $aead = null )
    {
        if (is_null($this->filesCache)) {
            $this->filesCache = File::fromTransfer($this);
        }

        // Check if already exists
        $matches = array_filter($this->filesCache, function ($file) use ($path, $size) {
            return ($file->path == $path) && ($file->size == $size);
        });
        
        if (count($matches)) {
            return array_shift($matches);
        }

        if (!Utilities::isValidFileName($path)) {
            throw new TransferFileNameInvalidException($path);
        }

        // Create and save new file
        $file = File::create($this, $path, $size, $mime_type);
        $file->iv = $iv;
        $file->aead = $aead;
        $file->save();
 
        // Update local cache
        $this->filesCache[$file->id] = $file;
        
        Logger::info($file.' added to '.$this);
        
        return $file;
    }
    
    /**
     * Removes a file
     *
     * @param mixed $file File or file id
     */
    public function removeFile($file)
    {
        if (!is_object($file)) {
            $file = File::fromId($file);
        }
        
        // Delete
        $file->delete();
        
        // Update local cache
        if (!is_null($this->filesCache) && array_key_exists($file->id, $this->filesCache)) {
            unset($this->filesCache[$file->id]);
        }
        
        Logger::info($file.' removed from '.$this);
    }

    /**
     * Adds a collection
     *
     * @param CollectionType $type the collection type
     * @param string $info unique information about the collection
     *
     * @return Collection
     */
    public function addCollection(CollectionType $type, $info)
    {
        $collections_added = false;
        
        if (is_null($this->collectionsCache)) {
            $this->collectionsCache = array();
            $collections_added = true;
        }
        
        $type_id = $type->id;
        $type_exists = array_key_exists($type_id, $this->collectionsCache);

        
        // Check if already exists
        if ($type_exists) {
            $matches = array_filter($this->collectionsCache[$type_id], function ($collection) use ($info) {
                return ($collection->info == $info);
            });
            
            if (count($matches)) {
                return array_shift($matches);
            }
        }

        // Create and save new recipient
        $collection = Collection::create($this, $type, $info);
        $collection->save();

        if ($collections_added) {
            $this->options[TransferOptions::COLLECTION] = true;
            $this->save();
        }
        
        // Update local cache
        if (!$type_exists) {
            $this->collectionsCache[$type_id] = array();
        }
        
        $this->collectionsCache[$type_id][$collection->id] = $collection;
        
        Logger::info($collection.' added to '.$this);
        
        return $collection;
    }

    /**
     * Removes a collection
     *
     * @param mixed $collection Collection or collection id
     */
    public function removeCollection($collection)
    {
        if (!is_object($collection)) {
            $collection = Collection::fromId($collection);
        }
        $type_id = $collection->type_id;
        $id = $collection->id;

        // Delete
        $collection->delete();
        
        // Update local cache
        if (!is_null($this->collectionsCache) &&
           array_key_exists($type_id, $this->collectionsCache) &&
           array_key_exists($id, $this->collectionsCache[$type_id])) {
            unset($this->collectionsCache[$type_id][$id]);
        }
        
        Logger::info($collection.' removed from '.$this);
    }
    
    /**
     * Adds a recipient
     *
     * @param string $email email to add as recipient
     *
     * @return Recipient
     */
    public function addRecipient($email)
    {
        // Check if already exists
        if (!is_null($this->recipientsCache)) {
            $matches = array_filter($this->recipientsCache, function ($recipient) use ($email) {
                return $recipient->email == $email;
            });
            
            if (count($matches)) {
                return array_shift($matches);
            }
        }
        
        // Create and save new recipient
        $recipient = Recipient::create($this, $email);
        $recipient->save();
        
        // Update local cache
        if (!is_null($this->recipientsCache)) {
            $this->recipientsCache[$recipient->id] = $recipient;
        }
        
        Logger::info($recipient.' added to '.$this.' with token '.$recipient->token);
        
        return $recipient;
    }
    
    /**
     * Test if some email address is in the recipient list
     *
     * @param string $email
     *
     * @return bool
     */
    public function isRecipient($email)
    {
        foreach ($this->recipients as $recipient) {
            if ($recipient->email == $email) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Removes a recipient
     *
     * @param mixed $recipient Recipient or recipient id
     */
    public function removeRecipient($recipient)
    {
        if (!is_object($recipient)) {
            $recipient = Recipient::fromId($recipient);
        }
        
        // Delete
        $recipient->delete();
        
        // Update local cache
        if (!is_null($this->recipientsCache) && array_key_exists($recipient->id, $this->recipientsCache)) {
            unset($this->recipientsCache[$recipient->id]);
        }
        
        Logger::info($recipient.' removed from '.$this);
    }
    
    /**
     * This function does stuffs when a transfer become available
     */
    public function makeAvailable()
    {
        if ($this->status == TransferStatuses::AVAILABLE) {
            return;
        } // Already available
        
        // Log to audit/stats that upload ended
        Logger::logActivity(LogEventTypes::UPLOAD_ENDED, $this);
        
        // Fail if no files
        if (!count($this->files)) {
            throw new TransferNoFilesException();
        }
        
        // Fail if any file not complete
        foreach ($this->files as $file) {
            if (!$file->upload_end) {
                throw new TransferFilesIncompleteException($this);
            }
        }
        
        // Fail if no recipients
        if (!count($this->recipients)) {
            throw new TransferNoRecipientsException();
        }

        $this->storage_filesystem_per_day_buckets = Config::get('storage_filesystem_per_day_buckets');
        $this->storage_filesystem_per_hour_buckets = Config::get('storage_filesystem_per_hour_buckets');
        
        // Update status and log to audit/stat
        $this->status = TransferStatuses::AVAILABLE;
        $this->made_available = time();
        $this->save();
        Logger::logActivity(LogEventTypes::TRANSFER_AVAILABLE, $this);
        
        if (Auth::isGuest()) {
            // If guest increase guest transfers counter
            $guest = AuthGuest::getGuest();
            
            $guest->transfer_count++;

            if( $this->guest_transfer_shown_to_user_who_invited_guest ) {
                // Send notification if required
                if ($this->getOption(TransferOptions::EMAIL_UPLOAD_COMPLETE)) {
                    TranslatableEmail::quickSend('guest_upload_complete', $guest->owner, $guest);
                }
            }

            // Let the guest know the upload is complete too
            // but if they can only 'send to me' then do not leak the download link
            if (!$guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
                TranslatableEmail::quickSend('guest_upload_complete_confirmation_to_guest', $guest, $this);
            }
            
            // Remove guest rights if valid for one upload only
            if ($guest->getOption(GuestOptions::VALID_ONLY_ONE_TIME)) {
                $guest->status = GuestStatuses::CLOSED;
            }
            
            $guest->save();
        } else {
            // If not guest send upload complete notification if required
            if ($this->getOption(TransferOptions::EMAIL_UPLOAD_COMPLETE)) {
                TranslatableEmail::quickSend('upload_complete', $this->owner, $this);
            }
            
            // Save transfer's recipient in the user's frequent recipients
            $this->owner->saveFrequentRecipients($this->recipients);
            
            // Save choosen transfer options in user preferences
            $this->owner->saveTransferOptions($this->options);
        }
        
        if (!$this->getOption(TransferOptions::GET_A_LINK)) {
            // Unless get_a_link mode process options
            
            if ($this->getOption(TransferOptions::ADD_ME_TO_RECIPIENTS)) {
                $rcpt = $this->user_email;

                if(Auth::isGuest()) {
                    $guest = AuthGuest::getGuest();
                    if($guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
                        $rcpt = $guest->user_email;
                    }
                }

                if(!$this->isRecipient($rcpt)) {
                    $this->addRecipient($rcpt);
                }
            }
            
            // Send notification of availability to recipients
            foreach ($this->recipients as $recipient) {
                $this->sendToRecipient('transfer_available', $recipient);
            }
            
            // Log to audit/stat
            Logger::logActivity(LogEventTypes::TRANSFER_SENT, $this, Auth::isGuest() ? AuthGuest::getGuest() : null);
        }
        
        Logger::info($this.' made available'.(Auth::isGuest() ? ' by '.AuthGuest::getGuest() : '').', took '.$this->made_available_time.'s');
    }
    
    /**
     * Send reminder to recipient(s)
     *
     * @param mixed $recipients single recipient or array of recipients (defaults to transfer recipients if not provided)
     */
    public function remind($recipients = null)
    {
        if ($this->getOption(TransferOptions::GET_A_LINK)) {
            return;
        }
        
        if (!$recipients) {
            $recipients = $this->recipients;
        }
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }
        
        foreach ($recipients as $recipient) {
            $this->sendToRecipient('transfer_reminder', $recipient);
        }
        
        Logger::info($this.' reminded to recipient(s)');
    }
    
    /**
     * Send automatic reminders
     */
    public static function sendAutomaticReminders()
    {
        $rms = Config::get('transfer_automatic_reminder');
        if (!$rms) {
            return;
        }
        
        if (!is_array($rms)) {
            $rms = array($rms);
        }
        
        $rms = array_filter($rms, function ($r) {
            return is_int($r) && ($r >= 1);
        });
        
        if (!count($rms)) {
            return;
        }
        
        foreach (self::all(self::AVAILABLE) as $transfer) {
            $recipients_downloaded_ids = array_map(function ($l) {
                return $l->author_id;
            }, $transfer->downloads);
            
            // Get recipients that did not download
            $recipients_no_download = array_filter(
                $transfer->recipients,
                function ($recipient) use ($recipients_downloaded_ids) {
                    return !in_array($recipient->id, $recipients_downloaded_ids) && (bool)$recipient->email;
                }
            );
            
            if (!count($recipients_no_download)) {
                continue;
            } // Nothing to notify
            
            $today = floor(time() / 86400);
            $created = floor($transfer->created / 86400);
            $expires = floor($transfer->expires / 86400);
            $lifetime = $expires - $created + 1;
            
            $days_remaining = $expires - $today + 1;
            
            if (!in_array($days_remaining, $rms)) {
                continue;
            } // No matching automatic reminders
            
            // Remind recipients
            foreach ($recipients_no_download as $recipient) {
                $recipient->remind();
            }

            $send_owner_autoreminder = true;

            // no not leak this transfer in a reminder if the system wants
            // private guests
            if( !$transfer->userCanSeeTransfer()) {
                $send_owner_autoreminder = false;
            }

            if( $send_owner_autoreminder ) {
                // Send receipt to owner
                ApplicationMail::quickSend(
                    'transfer_autoreminder_receipt',
                    $transfer->owner,
                    $transfer,
                    array(
                        'recipients' => $recipients_no_download
                    )
                );
            }
        }
    }
    
    /*
     * Start transfer and log
     */
    public function start()
    {
        $this->status = TransferStatuses::STARTED;
        $this->save();
        
        if (Auth::isGuest()) {
            // Send upload started notification if guest and guest owner required it
            $guest = AuthGuest::getGuest();

            if( $this->guest_transfer_shown_to_user_who_invited_guest ) {            
                if ($guest->getOption(GuestOptions::EMAIL_UPLOAD_STARTED)) {
                    TranslatableEmail::quickSend('guest_upload_start', $guest->owner, $guest);
                }
            }
        }
        
        Logger::logActivity(LogEventTypes::TRANSFER_STARTED, $this, Auth::isGuest()?AuthGuest::getGuest():null);
        Logger::info($this.' started'.(Auth::isGuest() ? ' by '.AuthGuest::getGuest() : ''));
    }
    
    /**
     * Set uploading and log
     */
    public function isUploading()
    {
        if ($this->status != TransferStatuses::STARTED) {
            return;
        }
        
        $this->status = TransferStatuses::UPLOADING;
        $this->save();
        Logger::logActivity(LogEventTypes::UPLOAD_STARTED, $this);
        Logger::info($this.' upload started');
    }
    
    
    /**
     * Send message to recipient, handling options
     *
     * @param string $translation_id lang string id
     * @param Recipient $recipient
     * @param mixed ... translation variables
     */
    public function sendToRecipient($translation_id, $recipient)
    {
        $args = func_get_args();
        $args[] = $this;
        
        $mail = call_user_func_array('TranslatableEmail::prepare', $args);
        
        if ($this->getOption(TransferOptions::EMAIL_ME_COPIES)) {
            $mail->bcc($this->user_email);
        }
        
        $mail->setDebugTemplate($translation_id);
        $mail->send();
        
        Logger::info('Mail#'.$translation_id.' sent to '.$recipient);
    }

    /**
     * uploading has completed. This is true for complete and closed
     * transfers and this method allows functions to check of an upload
     * is still in progress or not.
     */
    public function isStatusAtleastUploaded()
    {
        return $this->status == TransferStatuses::AVAILABLE ||
               $this->status == TransferStatuses::CLOSED;
    }
    
    /**
     * closed transfer.
     */
    public function isStatusClosed()
    {
        return $this->status == TransferStatuses::CLOSED;
    }
    
    /**
     * Call here when you want to deny state changes to already complete
     * transfers. Note that states 'less than' UPLOADING are considered OK
     * for this. We only want to deny changes to 'available' or closed transfers.
     */
    public function isStatusUploading()
    {
        return $this->status == TransferStatuses::CREATED ||
               $this->status == TransferStatuses::STARTED ||
               $this->status == TransferStatuses::UPLOADING;
    }

}
