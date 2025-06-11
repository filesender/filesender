<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2018, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 * Represents the collection of aggregate statistics.
 * These have running sums for time and size and are used
 * to produce charts in a timely fashion for series data.
 * 
 * create() is called by logger to capture events as they occur 
 * and this information is upserted into the statistics table
 * at many epoch levels. So a single event is tracked at 15 minutes
 * hourly, weekly etc levels.
 * 
 * sum and time fields use numeric types as they are expected to be
 * able to become truely huge. For example, the number of bytes uploaded
 * to a filesender instance over an entire year.
 */
class AggregateStatistic extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'epoch' => array(
            'type' => 'datetime',
            'primary' => true,
        ),
        'epochtype' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
        ),
        'eventtype' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
        ),
        // defaultable from here out
        'eventcount' => array(
            'type' => 'uint',
            'size' => 'big',
            'null' => true  // this is null because number/null => null instead of div by zero
        ),
        // as these are sums and they can be collected over a year interval in some cases
        // the numbers can become quite large.
        //
        // Consider a case where 100k users are uploading 1tb each per day
        // this will overflow a 64 bit integer for the sizesum for a complete year.
        // Using numeric/decimal types gets more headroom. 
        'timesum' => array(
            'type' => 'numeric',
            'default' => 0
        ),
        'sizesum' => array(
            'type' => 'numeric',
            'default' => 0
        ),
        'encryptedsum' => array(
            'type' => 'numeric',
            'default' => 0
        )
    );

    protected static $secondaryIndexMap = array();

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select agg.*'
                        . ', sizesum/eventcount as sizemean '
                        . ', timesum/eventcount as timemean '
                        . ', DBConstantEpochTypes.description  as epochtypetext '
                        . ', DBConstantStatsEvents.description as eventtypetext '
                        . '  from ' . self::getDBTable() . ' agg'
                                          . ' join DBConstantEpochTypes  on DBConstantEpochTypes.id=agg.epochtype '
                                          . ' join DBConstantStatsEvents on DBConstantStatsEvents.id=agg.eventtype ';

        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Properties
     */
    protected $epoch = null;
    protected $epochtype = null;
    protected $eventtype = null;
    protected $eventcount = null;
    protected $timesum = 0;
    protected $sizesum = 0;
    protected $encryptedsum = 0;
    
    
    /**
     * Constructor
     *
     * @param integer $id identifier of user to load from database (null if loading not wanted)
     * @param array $data data to create the user from (if already fetched from database)
     *
     * @throws UserNotFoundException
     */
    public function __construct($epoch=null,$epochtype=null,$eventtype=null, $data = null)
    {
        if (!is_null($epoch) && !is_null($epochtype) && !is_null($eventtype)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE '
                . ' epoch = :epoch AND epochtype = :epochtype AND eventtype = :eventtype');
            $statement->execute(array(':epoch' => $epoch,
                                      ':epochtype' => $epochtype,
                                      ':eventtype' => $eventtype ));
            $data = $statement->fetch();
            if (!$data) {
                throw new StatLogNotFoundException(
                    "epoch=$epoch, epochtype=$epochtype, eventtype=$eventtype");
            }
        }

        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }

    public static function enabled()
    {
        $lt = Config::get('aggregate_statlog_lifetime');
        if (is_null($lt) || (is_bool($lt) && !$lt)) {
            return false;
        } 
        return true;
    }

    /**
     * Some events might want to upsert two (or more) event types depending on other
     * aspects of the data. For example, if a transfer was encrypted or not.
     */
    public static function upsertEvent($event, $eventtype, $epoch, $sizeadd, $timeadd )
    {
        if( $eventtype ) {
            // upsert each epochType as they broaden.
            $e = new EpochType( DBConstantEpochType::NARROWEST_TYPE, $epoch );
            for( ; $e; $e = $e->broaden() ) {
                
                $epochVal = "'".date('Y-m-d H:i:s', $e->tt) . "'";
                Logger::debug('event ' . $event . ' looking up ' . $e->epochType );
                $et = DBConstantEpochType::lookup($e->epochType);
                
                DatabaseUpsert::upsert( 
                    "insert into AggregateStatistics "
                  . " (epoch,epochtype,eventtype,eventcount,timesum,sizesum) "
                  . " values ( $epochVal,$et,$eventtype,1,$timeadd,$sizeadd ) "
                  , "epoch,epochtype,eventtype"
                  , " eventcount=AggregateStatistics.eventcount+1"
                  . "  , sizesum = AggregateStatistics.sizesum+$sizeadd "
                  . "  , timesum = AggregateStatistics.timesum+$timeadd "
                );
                
            }
        }
    }

    /**
     * This is like upsertEvent() but the largest value is retained instead
     * of the sum.
     */
    public static function upsertLargestEvent($event, $eventtype, $epoch, $sizeadd, $timeadd )
    {
        // eventtype is the base event such as UPLOAD_ENDED
        if( $eventtype == DBConstantStatsEvent::lookup(DBConstantStatsEvent::UPLOAD_ENDED)) {
            $eventtype = DBConstantStatsEvent::lookup(DBConstantStatsEvent::UPLOAD_MAXSIZE_ENDED);
        } else {
            return;
        }
        
        if( $eventtype ) {
            // upsert each epochType as they broaden.
            $e = new EpochType( DBConstantEpochType::NARROWEST_TYPE, $epoch );
            for( ; $e; $e = $e->broaden() ) {
                
                $epochVal = "'".date('Y-m-d H:i:s', $e->tt) . "'";
                Logger::debug('event ' . $event . ' looking up ' . $e->epochType );
                $et = DBConstantEpochType::lookup($e->epochType);
                
                DatabaseUpsert::upsert( 
                    "insert into AggregateStatistics "
                  . " (epoch,epochtype,eventtype,eventcount,timesum,sizesum) "
                  . " values ( $epochVal,$et,$eventtype,1,$timeadd,$sizeadd ) "
                  , "epoch,epochtype,eventtype"
                  , " eventcount=AggregateStatistics.eventcount+1"
                  . "  , sizesum = GREATEST(AggregateStatistics.sizesum,$sizeadd) "
                  . "  , timesum = GREATEST(AggregateStatistics.timesum,$timeadd) "
                );
                
            }
        }
    }

    
    /**
     * Create a new stat log
     *
     * @param StatEvent $event: the event to be logged
     * @param DBObject: the target to be logged
     *
     * @return StatLog auditlog
     */
    public static function create($event, DBObject $target)
    {
        if( !self::enabled()) {
            return;
        }

        // Check type
        if (!LogEventTypes::isValidValue($event)) {
            throw new StatLogUnknownEventException($event);
        }


        $sizeadd = 0;
        $timeadd = 0;
        
        // Get metadata depending on target
        switch (get_class($target)) {
            case File::getClassName():
                $sizeadd = $target->size;
                if ($event == LogEventTypes::FILE_UPLOADED) {
                    $timeadd = $target->upload_time;
                }
                break;
            
            case Transfer::getClassName():
                $sizeadd = $target->size;
                
                if ($event == LogEventTypes::UPLOAD_ENDED) {
                    $timeadd = $target->upload_time;
                }
                if ($event == LogEventTypes::TRANSFER_AVAILABLE) {
                    $timeadd = $target->made_available_time;
                }
                break;
        }
        
        $epoch = time();
        $eventtype = DBConstantStatsEvent::fromLogEventType($event);

        self::upsertEvent( $event, $eventtype, $epoch, $sizeadd, $timeadd );
        self::upsertLargestEvent( $event, $eventtype, $epoch, $sizeadd, $timeadd );
        
        // Some times a single event is split into two upserts using
        // different eventtypes for aggregate stats. We record as well
        // as overall bytes and count the totals
        // for encrypted and nocrypt transfers.
        $derivedET = DBConstantStatsEvent::augmentToEventConsideringEncryption( $eventtype, $target );
        if( $derivedET != $eventtype ) {
            self::upsertEvent( $event, $derivedET, $epoch, $sizeadd, $timeadd );
            self::upsertLargestEvent( $event, $derivedET, $epoch, $sizeadd, $timeadd );
        }
    }
    
    /**
     * Check how much storage is used and update the aggregate statistics
     * to include information about how much of the storage is used at the moment.
     *
     * transfers which are expired are also considered so this call should be done
     * in the cron script before storage is reclaimed from expired transfers.
     */
    public static function cronAnalyseStorage()
    {
        $expiredSize = 0;
        $epoch = time();
        $timeadd = 0;
        
        foreach(Transfer::allExpired() as $transfer) {
            if($transfer->status == TransferStatuses::CLOSED) {
                continue;
            }
            $sz = $transfer->size;
            Logger::info($transfer.' adding to count size ' . $sz);
            $expiredSize += $sz;
        }

        Logger::info('total expired size is ' . $expiredSize);
        $eventtype = DBConstantStatsEvent::lookup(
            DBConstantStatsEvent::STORAGE_EXPIRED_TRANSFERS_SIZE);
        self::upsertEvent('unknown', $eventtype, $epoch, $expiredSize, $timeadd );


        $storage_usage = Storage::getUsage();
        if(!is_null($storage_usage)) {
            $total_space = 0;
            $free_space = 0;
            foreach($storage_usage as $info) {
                $total_space += $info['total_space'];
                $free_space += $info['free_space'];
            }
            $used_space = $total_space - $free_space;

            self::upsertEvent('unknown',
                              DBConstantStatsEvent::lookup(
                                  DBConstantStatsEvent::STORAGE_USED_SIZE),
                              $epoch, $used_space, $timeadd );
            self::upsertEvent('unknown',
                              DBConstantStatsEvent::lookup(
                                  DBConstantStatsEvent::STORAGE_FREE_SIZE),
                              $epoch, $free_space, $timeadd );
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
    public function __get($property)
    {
        if( array_key_exists($property, self::getDataMap())) {
            return $this->$property;
        }
        throw new PropertyAccessException($this, $property);
    }

    /**
     * If aggregate stats are enabled and emailing stats is enabled then:
     * 
     * If enough time has passed since we last sent a report then we create
     * a new ones and email it to the nominated email address for such reports.
     */
    public static function maybeSendReport()
    {
        // if they have not enabled this feature then do nothing.
        if( !self::enabled()) {
            return;
        }

        $md = AggregateStatisticMetadata::ensure();
        $age = AggregateStatisticMetadata::getLastSentDaysAgo();

        $sendage   = Config::get('aggregate_statlog_send_report_days');
        $emailaddr = Config::get('aggregate_statlog_send_report_email_address');

        Logger::info("checking if we should send an aggregate statistics report age: $age\n");

        if( !$emailaddr || !strlen($emailaddr)) {
            Logger::warn("please set aggregate_statlog_send_report_email_address"
                       . " to where you would like the aggregate statistics to be sent\n");
            return;
        }
        
        if( $sendage > 0 && $age > $sendage ) {

            Logger::info("sending an aggregate statistics report\n");

            // dump the two tables of interest to attachments
            // and send them to the configured email address.
            $mail = new ApplicationMail('filesender aggregate stats');
            $mail->to($emailaddr);
            
            $statement = DBI::prepare('SELECT * FROM '.AggregateStatisticMetadata::getDBTable());
            $statement->execute(array());
            $attachment = new MailAttachment('metadata.json');
            $attachment->content = json_encode($statement->fetchAll());
            $mail->attach($attachment);

            $statement = DBI::prepare('SELECT * FROM '.AggregateStatistic::getDBTable());
            $statement->execute(array());
            $attachment = new MailAttachment('stats.json.gz');
            $data = gzencode( json_encode($statement->fetchAll()));
            $attachment->content = $data;
            $mail->attach($attachment);
            
            $mail->send();

            $md = AggregateStatisticMetadata::ensure();
            $md->lastsend = time();
            $md->save();

            Logger::info("sent an aggregate statistics report\n");
        }
    }
}
