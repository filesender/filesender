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


if(!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return \strncmp($haystack, $needle, \strlen($needle)) === 0;
    }
}
    
/**
 * Represents an user in database
 */
class RateLimitHistory extends DBObject
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
        'created' => array(
            'type' => 'datetime'
        ),
        'author_context_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'author_context_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'action' => array(
            'type' => 'string',
            'size' => 40
        ),
        'event' => array(
            'type' => 'string',
            'size' => 255
        ),
        'target_context_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'target_context_id' => array(
            'type' => 'string',
            'size' => 255,
            'null' => true
        ),
    );

    
    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }

    protected static $secondaryIndexMap = array(
        'action_event_created' => array(
            'action' => array(),
            'event' => array(),
            'created' => array()
        )
    );
    
    
    /**
     * Properties
     */
    protected $id                  = null;
    protected $created             = null;
    protected $author_context_type = null;
    protected $author_context_id   = null;
    protected $action              = null;
    protected $event               = null;
    protected $target_context_type = null;
    protected $target_context_id   = null;

    /**
     * Constructor
     *
     * @param integer $id identifier of object to load from database (null if loading not wanted)
     * @param array $data data to create the object from (if already fetched from database)
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
        } else {
            // New user, set base data
            $this->id = $id;
            $this->created = time();
        }
    }

    /**
     * Create a new RateLimitHistory record
     *
     * @return RateLimitHistory
     */
    public static function create( $author_context_type, $author_context_id
                                 , $action, $event
                                 , $target_context_type, $target_context_id )
    {
        $r = new self();
        $r->created = time();
        $r->author_context_type = $author_context_type;
        $r->author_context_id   = $author_context_id;
        $r->action              = $action;
        $r->event               = $event;
        $r->target_context_type = $target_context_type;
        $r->target_context_id   = $target_context_id;
        return $r;
    }

    /**
     * Check if this action/event is over the rate limit or not.
     * 
     * Throw an exception if the limit has been reached.
     *
     * If $updateDatabase is set then the action is recorded in the database
     * if you are performing a check before performing an action then 
     * set $updateDatabase=false
     * 
     */   
    public static function rateLimit( $updateDatabase
                                    , $author_context_type, $author_context_id
                                    , $action, $event
                                    , $target_context_type, $target_context_id )
    {        
        $count = self::countEntries( $author_context_type, $author_context_id
                                   , $action, $event
                                   , $target_context_type, $target_context_id );

        $r = -1;

        $rates = Config::get('rate_limits');
        
        if( array_key_exists( $action, $rates )) {
            $cfg = $rates[$action];
            if( array_key_exists( $event, $cfg )) {
                $cfg = $cfg[$event];
                if( array_key_exists( 'day', $cfg )) {
                    $r = $cfg['day'];
                }
            }
        }

        
        if( $r != -1 && $count >= $r ) {
            throw new RateLimitException( $author_context_type, $author_context_id
                                        , $action, $event );
        }
        
        //
        // If we get here then the action is likely to have been performed already
        // for example, creating a guest
        // so we should update the counter *after* checking that we are not already
        // over the limit.
        //
        if( $updateDatabase ) {
            $ratehistory = self::create( $author_context_type, $author_context_id
                                       , $action, $event
                                       , $target_context_type, $target_context_id );
            $ratehistory->save();
        }
    }
    
    public static function countEntries( $author_context_type, $author_context_id
                                       , $action, $event
                                       , $target_context_type, $target_context_id )
    {
        $ret = 0;

        $secondsAgo = 24*3600;
        $created = time() - $secondsAgo;
        $includeTargetID = false;
        
        if( $target_context_type == 'Transfer' ) {
            if( $action == 'email' && $event == 'transfer_available' ) {
                // only count stats for transfer_available using the author context.
                // when creating objects we shouldn't include their new id in the
                // test or we will not have any limit action
            } else {
                $includeTargetID = true;
            }
        }
        if( $target_context_type == 'User' ) {
            $includeTargetID = true;
        }
        if( $target_context_type == 'Guest' ) {
            if( $action == 'email' && str_starts_with( $event, 'guest_created' )) {
                // do not count the ID of the new guest or the system will never rate limit
                // create operations
            } else {
                $includeTargetID = true;
            }
        }

        $sql = 'SELECT count(*) as count, 1 as one '
             . ' FROM ' . self::getDBTable()
                              . ' WHERE author_context_type = :author_context_type '
                              . ' AND   author_context_id   = :author_context_id '
                              . ' AND   action              = :action '
                              . ' AND   event               = :event '
                              . ' AND   target_context_type = :target_context_type '
                              . ' AND   created >= :created ';
        $params = array(
            ':author_context_type'   => $author_context_type
          , ':author_context_id'   => $author_context_id
          , ':action'              => $action
          , ':event'               => $event
          , ':target_context_type' => $target_context_type
          , ':created' => date('Y-m-d H:0:0', $created)
        );

        $vvvv = date('Y-m-d H:0:0', $created);
        if( $includeTargetID ) {
            $sql .= ' AND   target_context_id   = :target_context_id ';
            $params[':target_context_id'] = $target_context_id;
        }
        $statement = DBI::prepare( $sql );
        $statement->execute( $params );
        $data = $statement->fetch();
        if( $data ) {
            $ret = $data['count'];
        }
        
        return $ret;
    }
    
    public function __get($property)
    {
        if (in_array($property, array(
            'id', 'created'
            , 'author_context_type', 'author_context_id'
            , 'action',              'event'
            , 'target_context_type', 'target_context_id'
        ))) {
            return $this->$property;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    

    public static function cleanup()
    {
        $dbtype = Config::get('db_type'); 

        // Keep these logs for 31 days
        $lifetime = Config::get('ratelimithistory_lifetime');
        if (!is_null($lifetime) && $lifetime > 0) {
            // delete auditlogs entries that are too old
            $statement = DBI::prepare("delete from ".self::getDBTable()." where created < :cutoff ");
            $statement->execute(array(':cutoff' => date('Y-m-d H:i:s', time() - ($lifetime*24*3600))));
        }
    }

    
}

    
