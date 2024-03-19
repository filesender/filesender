<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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
class AuditLog extends DBObject
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
        'event' => array(
            'type' => 'string',
            'size' => 32
        ),
        'target_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'target_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'author_type' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'author_id' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
        'ip' => array(
            'type' => 'string',
            'size' => 39,
        ),
        'transaction_id' => array(
            'type' => 'string',
            'size' => 36,
            'null' => true
        ),
        'created' => array(
            'type' => 'datetime'
        )
    );

    protected static $secondaryIndexMap = array(
        'Type_ID' => array(
            'target_type' => array(),
            'target_id'   => array()
        ),
        'Author_ID' => array(
            'author_type' => array(),
            'author_id'   => array()
        ),
        'Transaction_ID' => array(
            'transaction_id' => array()
        ),
        'Created' => array(
            'created' => array()
        ),
        // This is for FROM_TARGET_AND_AUTHOR_SINCE which is used by Logger::rateLimit()
        'created_event_ttype_tid' => array(
            'created' => array(),
            'event' => array(),
            'target_type' => array(),
            'target_id'   => array()
        )            
        
//        'Type_ID_AType_AID_IP_Event_Created' => array(
//            'target_type' => array(),
//            'target_id'   => array(),
//            'author_type' => array(),
//            'author_id'   => array(),
//            'ip'          => array(),
//            'event'       => array(),
//            'created'     => array()
//        ),
        
    );

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . DBView::columnDefinition_as_number($dbtype, 'target_id')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Set selectors
     */
    const FROM_TARGET = 'target_type = :type AND target_id = :id ORDER BY created ASC, id ASC';
    const FROM_AUTHOR = 'author_type = :type AND author_id = :id ORDER BY created ASC, id ASC';
    const FROM_TARGET_AND_AUTHOR = 'event = :event AND target_type = :ttype AND target_id = :tid AND author_type = :atype AND author_id = :aid ORDER BY created DESC limit 10 ';
    const FROM_TARGET_AND_AUTHOR_SINCE = 'created > :created AND event = :event AND target_type = :ttype AND target_id = :tid AND author_type = :atype AND author_id = :aid ';    
    const FROM_TARGET_TYPE_SINCE = 'created > :created AND event = :event AND target_type = :ttype  ';    
    const FIND_USERS_SINCE = array( 'select' => 'max(id) as id,target_id',
                                    'where' => 'created > :created AND event = :event AND target_type = :ttype ',
                                    'group' => 'target_id' );
    const FIND_USERS_SINCE_GAID = array( 'select' => 'max(id) as id,author_id',
                                    'where' => 'created > :created AND event = :event AND target_type = :ttype ',
                                    'group' => 'author_id' );
    
    /**
     * Properties
     */
    protected $id = null;
    protected $event = null;
    protected $target_type = null;
    protected $target_id = null;
    protected $author_type = null;
    protected $author_id = null;
    protected $created = null;
    protected $ip = null;
    protected $transaction_id = null;
    
    
    /**
     * Constructor
     *
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the auditlog from (if already fetched from database)
     *
     * @throws AuditLogNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new AuditLogNotFoundException('id = '.$id);
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Create a new audit log
     *
     * @param LogEventTypes $event the event to be logged
     * @param DBObject the target to be logged
     * @param DBObject the author of the action
     *
     * @return AuditLog auditlog
     */
    public static function create($event, DBObject $target, $author = null)
    {
        if (is_null(Config::get('auditlog_lifetime'))) { // Auditlog disabled
            return;
        }
        
        // Check event type
        if (!LogEventTypes::isValidValue($event)) {
            throw new AuditLogUnknownEventException($event);
        }
        
        $auditLog = new self();
        
        $auditLog->event = $event;
        $auditLog->created = time();
        $auditLog->ip = Utilities::getClientIP();
        $auditLog->target_id = $target->id;
        $auditLog->target_type = get_class($target);
        
        if(array_key_exists('transaction_id', $_REQUEST)) {
            $transaction_id = $_REQUEST['transaction_id'];
            if(Utilities::isValidUID($transaction_id)) {
                $auditLog->transaction_id = $transaction_id;
            }
        }

        if (!$author) {
            $author = Auth::user();
        }
        
        if (is_object($author)) {
            $auditLog->author_type = get_class($author);
            $auditLog->author_id = $author->id;
        }
        
        $auditLog->save();
        
        return $auditLog;
    }
    
    /**
     * Save in database
     */
    public function save()
    {
        $this->insertRecord($this->toDBData());
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
            'id',
            'event',
            'target_type',
            'target_id',
            'author_type',
            'author_id',
            'created',
         ))) {
            return $this->$property;
        }
 
        if ($property == 'ip') {
            //Strip out ::ffff: from ipv4 addresses when in ipv6 mode
            $ip = preg_replace('/^::ffff:([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)$/', '$1', $this->ip);

            //Get hostname and store it for speed
            if (!isSet($_SESSION['hosts'])) {
                $_SESSION['hosts']=array();
            }
            if (!isSet($_SESSION['hosts'][$ip])) {
                $_SESSION['hosts'][$ip]=gethostbyaddr($ip);
            }
            $host = $_SESSION['hosts'][$ip];
            if ($host!==false && $host!=$ip) {
                return $host.' ('.$ip.')';
            }
            return $ip;
        }
        
        if ($property == 'target') {
            try {
                return call_user_func($this->target_type.'::fromId', $this->target_id);
            } catch (Exception $e) {
                return (object)array(
                    'made_available_time' => 1,
                    'upload_time' => 1,
                    'name' => 'unknown',
                    'size' => 1,
                    'email' => 'unknown',
                    'id' => -1
                );
            }
        }
        
        if ($property == 'author') {
            if (!$this->author_type || !$this->author_id) {
                return null;
            }
            
            try {
                return call_user_func($this->author_type.'::fromId', $this->author_id);
            } catch (Exception $e) {
                return (object)array(
                    'identity' => 'unknown',
                    'email' => 'unknown',
                    'id' => -1
                );
            }
        }
        
        if ($property == 'time_taken') {
            if ($this->target_type == 'Transfer') {
                if ($this->event == LogEventTypes::TRANSFER_AVAILABLE) {
                    return $this->target->made_available_time;
                }
                if ($this->event == LogEventTypes::UPLOAD_ENDED) {
                    return $this->target->upload_time;
                }
            }
            
            if ($this->target_type == 'File') {
                if ($this->event == LogEventTypes::FILE_UPLOADED) {
                    return $this->target->upload_time;
                }
            }
            
            return 0;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Get logs related to a target
     *
     * @param DBObject $transfer
     *
     * @return array of AuditLog
     */
    public static function fromTarget(DBObject $target, $event = null)
    {
        $logs = self::all(self::FROM_TARGET, array('type' => $target->getClassName(), 'id' => (string)$target->id));
        
        if ($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function ($log) use ($event) {
                return $log->event == $event;
            });
        }
        
        return $logs;
    }
    
    /**
     * Get logs related to an author
     *
     * @param DBObject $author
     *
     * @return array of AuditLog
     */
    public static function fromAuthor(DBObject $author, $event = null)
    {
        $logs = self::all(self::FROM_AUTHOR, array('type' => $author->getClassName(), 'id' => (string)$author->id));
        
        if ($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function ($log) use ($event) {
                return $log->event == $event;
            });
        }
        
        return $logs;
    }

    /**
     * Get the most recent audit entry for a specific {$logEvent, $target, $author} 
     *
     * @param string from LogEventTypes
     * @param DBObject the target to be logged
     * @param DBObject the author of the action
     * 
     * @return the database entry for the last log item or array()
     */
    public static function latestEntry($logEvent, $target, $author = null )
    {
        if (!$author) {
            $author = Auth::user();
        }
        
        $logs = self::all(self::FROM_TARGET_AND_AUTHOR,
                          array(
                              'event' => $logEvent,
                              'ttype' => $target->getClassName(), 'tid' => (string)$target->id,
                              'atype' => $author->getClassName(), 'aid' => (string)$author->id
                          ));
        if( is_array($logs)) {
            // first element
            return reset($logs);
        }
        return array();
    }


    /**
     * This is a mirror of AuditLog::create() but you want to find the number 
     * of auditlog entries with the same {$logEvent, $target, $author} since a given number
     * of seconds ago. This way you can use the same parameters to a call to AuditLog::create()
     * and countEntries() to rate limit how many times something can happen in a given window of time
     *
     * If you just want to rate limit access to a code path please see
     * Logger::logActivityRateLimited().
     *
     * @param string from LogEventTypes
     * @param DBObject the target to be logged
     * @param DBObject the author of the action
     * @param int secondsAgo where to start the count. Defauts to 1 day in seconds.
     * @param DBObject $author
     * 
     */
    public static function countEntries($logEvent, $target, $secondsAgo = null, $author = null )
    {
        if(!$secondsAgo) {
            $secondsAgo = 24*3600;
        }
        $created = time() - $secondsAgo;
        
        if (!$author) {
            $author = Auth::user();
        }

        $statement = DBI::prepare('select count(*) as count '
                                . ' from ' . self::getDBTable() . ' where '
                                . self::FROM_TARGET_AND_AUTHOR_SINCE );
        $statement->execute(array(
            'created' => date('Y-m-d H:0:0', $created),
            'event' => $logEvent,
            'ttype' => $target->getClassName(), 'tid' => (string)$target->id,
            'atype' => $author->getClassName(), 'aid' => (string)$author->id
        ));
        $data = $statement->fetch();
        $c = $data['count'];
        return $c;
    }
    
    public static function findUsers($logEvent,$ttype,$secondsAgo = null)
    {
        if(!$secondsAgo) {
            $secondsAgo = 28*24*3600;
        }
        $created = time() - $secondsAgo;

        $query = self::FIND_USERS_SINCE;
        if( $ttype == 'Guest' ) {
            $query = self::FIND_USERS_SINCE_GAID;
        }
        $logs = self::all($query,
                          array(
                             'created' => date('Y-m-d H:0:0', $created),
                              'event' => $logEvent,
                              'ttype' => $ttype
                          ));
        $ret = array();
        foreach ($logs as $log) {
            if( $ttype == 'Guest' ) {
                $ret[] = User::fromId($log->author_id);
            } else {
                $ret[] = User::fromId($log->target_id);
            }
        }
        return $ret;
    }


    
    public static function findUsersOrderedByCount($logEvent,$atype = 'User',$secondsAgo = null)
    {
        if(!$secondsAgo) {
            $secondsAgo = 28*24*3600;
        }
        $created = time() - $secondsAgo;

        $statement = DBI::prepare('select count(event) as count,event,author_type,author_id from '.self::getDBTable()
                                . ' where event=:event AND author_type=:atype AND created > :created  '
                                . ' group by event,author_type,author_id order by count desc');
        $statement->execute(array(
            'created' => date('Y-m-d H:0:0', $created),
            'event' => $logEvent,
            'atype' => $atype
        ));
        $records = $statement->fetchAll();
        $ret = array();
        foreach ($records as $r) {
            $u = User::fromId($r['author_id']);
            $u->eventcount = $r['count'];
            $ret[] = $u;
        }
        return $ret;
    }
    

    
    /**
     * Get logs related to a transfer
     *
     * @param Transfer $transfer
     *
     * @return array of AuditLog
     */
    public static function fromTransfer(Transfer $transfer, $event = null, $since = null )
    {
        if (
            !is_object($transfer)
            || !$transfer->id
        ) {
            throw new TransferNotFoundException($transfer->id);
        }
        
        // Get and delete all audit logs related to the transfer
        $logs = array_values(self::all(self::FROM_TARGET, array('type' => $transfer->getClassName(), 'id' => (string)$transfer->id)));
        
        // Add events related to the transfer's files
        foreach (self::all('target_type=\'File\' AND target_id IN :ids', array(':ids' => array_map(function ($file) {
            return $file->id;
        }, $transfer->files))) as $log) {
            $logs[] = $log;
        }
        
        // Add events related to the transfer's recipients
        foreach (self::all('target_type=\'Recipient\' AND target_id IN :ids', array(':ids' => array_map(function ($recipient) {
            return $recipient->id;
        }, $transfer->recipients))) as $log) {
            $logs[] = $log;
        }
         
        
        // Sort by event date
        usort($logs, function ($a, $b) {
            $d = $a->created - $b->created;
            if ($d != 0) {
                return $d;
            }
            return $a->id - $b->id;
        });
        
        // filter by type if required
        if ($event && LogEventTypes::isValidValue($event)) {
            $logs = array_filter($logs, function ($log) use ($event) {
                return $log->event == $event;
            });
        }

        // Filter out older events.
        if( $since ) {
            $cutoff = time() - $since;
            
            $logs = array_filter($logs, function ($log) use ($cutoff) {
                return $log->created >= $cutoff;
            });
        }
        
        return $logs;
    }
    
    /**
     * Tells wether the client already downloaded the file set over a past range
     *
     * @param Recipient $recipient
     * @param array $files_ids
     * @param int $range in seconds
     *
     * @return bool
     */
    public static function clientRecentlyDownloaded($recipient, $files_ids, $range = 3600)
    {
        // Get a single download for each file if it exists over the range
        $downloaded = self::all(
            'target_type=\'File\' AND target_id IN :ids AND author_type=\'Recipient\' AND author_id=:rcptid AND ip = :ip AND event=\'download_ended\' AND created > :since GROUP BY target_id, id',
            array(
                ':ids' => $files_ids,
                ':rcptid' => $recipient->id,
                ':ip' => Utilities::getClientIP(),
                ':since' => date('Y-m-d H:i:s', time() - $range)
            )
        );
        
        // Same number of items means all files where downloaded (individually or not) over the range
        return count($downloaded) == count($files_ids);
    }
    
    /**
     * Remove entries related to a transfer
     *
     * @param Transfer $transfer
     */
    public static function clean(Transfer $transfer)
    {
        foreach (self::fromTransfer($transfer) as $log) {
            $log->delete();
        }
    }

    public static function cleanup()
    {
        $dbtype = Config::get('db_type'); 

        // update all AuditLogs Guest targets that point to guests
        // that have already been deleted from the guests table
        // to have a 'null' target_id perparing them for deletion
        DBI::exec(
            ""
          . "update ".self::getDBTable()." a "
                          . " set target_id = 'null' "
                          . " where a.target_type = 'Guest' "
                          . " and 0=(select count(*) as c from Guests g "
                          . "        where g.id = ".DBView::cast_as_number($dbtype,"a.target_id")
                          . "        limit 1"
                          ."        ) "
        );
        // delete AuditLogs entries for a guest target where they
        // have a null target_id
        DBI::exec(
            ""
          . "delete from ".self::getDBTable()." where target_id = 'null' and target_type = 'Guest' "
        );
        
        // if there is a sunset lifetime for the auditlog
        // then cleanup records that are too old.
        $lifetime = Config::get('auditlog_lifetime');
        if (!is_null($lifetime) && $lifetime > 0) {
            // delete auditlogs entries that are too old
            $statement = DBI::prepare("delete from ".self::getDBTable()." where created < :cutoff ");
            $statement->execute(array(':cutoff' => date('Y-m-d H:i:s', time() - ($lifetime*24*3600))));
        }
    }

    public function createdWithinLastDay() {
        return $this->created > (time() - 24*3600);
    }


    public static function getUsersForTargetTypeSince($logEvent, $ttype, $secondsAgo = null )
    {
        if(!$secondsAgo) {
            $secondsAgo = 24*3600;
        }
        $created = time() - $secondsAgo;

        $sql  = '( select MAX(id) as id, event, MAX(target_type) as target_type, MAX(target_id) as target_id, author_type, author_id, MAX(ip) as ip, MAX(created) as created,count(*) as count ';
        $sql .= ' from ' . self::getDBTable();
        $sql .= ' where ';
        $sql .=    self::FROM_TARGET_TYPE_SINCE;
        $sql .= ' and author_id is not null ';
        $sql .= ' group by event,author_type,author_id ) ';
        $sql .= ' UNION ';
        $sql .= '( select MAX(id) as id, event, MAX(target_type) as target_type, MAX(target_id) as target_id, author_type, author_id, ip, MAX(created) as created,count(*) as count ';
        $sql .= ' from ' . self::getDBTable();
        $sql .= ' where ';
        $sql .=    self::FROM_TARGET_TYPE_SINCE;
        $sql .= ' and author_id is null and author_type is null';
        $sql .= ' group by event,author_type,author_id,ip )';
        $sql .= ' order by count desc ';

        $statement = DBI::prepare($sql);
        $statement->execute(array(
            'created' => date('Y-m-d H:0:0', $created),
            'event' => $logEvent,
            'ttype' => $ttype
        ));

        $records = $statement->fetchAll();
        $ret = array();
        foreach ($records as $r) {
            $userid = $r['author_id'];
            if( $userid ) {
                $u = User::fromId($userid);
            } else {
                $u = User::createFactory(null);
            }
            $u->eventcount = $r['count'];
            $u->eventip    = $r['ip'];
            $ret[] = $u;
        }
        return $ret;
        
    }

}
