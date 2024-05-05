<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2024, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
 * Upload graph statistics for display to users
 */
class UploadGraph extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'date' => array(
            'type' => 'datetime',
            'primary' => true,
        ),
        'speed' => array(
            'type' => 'numeric',
            'null' => true,
        ),
        'enspeed' => array(
            'type' => 'numeric',
            'null' => true,
        ),
        'count' => array(
            'type' => 'numeric',
            'null' => true,
        ),
        'encount' => array(
            'type' => 'numeric',
            'null' => true,
        ),
    );

    

    protected static $secondaryIndexMap = array(
        'date' => array(
            'date' => array()
        )
    );
    
    
    /**
     * Properties
     */
    protected $id                  = null;
    protected $date                = null;
    protected $speed               = 0;
    protected $enspeed             = 0;
    protected $count               = 0;
    protected $encount             = 0;

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
            $this->date = time();
        }
    }

    /**
     * Create a new UploadGraph record
     *
     * @return UploadGraph
     */
    public static function create( $date, $speed, $enspeed, $count, $encount )
    {
        $r = new self();
        $r->date = $date;
        $r->speed = $speed;
        $r->enspeed = $enspeed;
        $r->count = $count;
        $r->encount = $encount;
        return $r;
    }

    
    public function __get($property)
    {
        if (in_array($property, array(
            'id', 'date'
          , 'speed', 'enspeed'
          , 'count', 'encount'
        ))) {
            return $this->$property;
        }
        
        throw new PropertyAccessException($this, $property);
    }
    

    public static function update()
    {
        $dbtype = Config::get('db_type');

        

        
        $minSz = Config::get('upload_graph_bulk_min_file_size_to_consider');

        $encO = Config::get('encryption_mandatory');
        $encF = 'additional_attributes LIKE \'%\"encryption\":false%\'';
        $encT = 'additional_attributes LIKE \'%\"encryption\":true%\'';

        $statement = DBI::prepare("delete from " . self::getDBTable() . " where date < NOW() - INTERVAL '31' DAY  " );
        $statement->execute(array());
        
        if( $dbtype == "mysql" ) { 
        
            $sql =
                ' INSERT INTO ' . self::getDBTable() . ' (date,speed,enspeed,count,encount) ' 
                                      . ' SELECT days.date as date, speed.speed as speed, speed.enspeed as enspeed, speed.count as count, speed.encount as encount '
                                      . 'FROM (SELECT (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
                                      . DBLayer::toIntervalDays("a+b") . ' date '
                                               . '        FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 '
                                               . '                     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 ) d, '
                                               . '             (SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) m '
                                               . '        WHERE (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
                                               . DBLayer::toIntervalDays("a+b") . ' <= (select date(now())) '
                                                        . '        ORDER BY a + b) as days LEFT '
                                                        . ' JOIN (SELECT DATE(created) as date, '
                                                         .($encO ? '0 as speed, ' : '   AVG(case WHEN time_taken > 0 AND ' . $encF . ' THEN size/time_taken ELSE null END) as speed, ' )
                                                        . '   AVG(case WHEN time_taken > 0 AND ' . $encT . ' THEN size/time_taken ELSE null END) as enspeed, '
                                                         .($encO ? '0 as count, ' : '   AVG(case WHEN ' . $encF . ' THEN id ELSE null END) as count, ' )
                                                        . '   AVG(case WHEN ' . $encT . ' THEN id ELSE null END) as encount '
                                                        . '       from StatLogs '
                                                        . '      WHERE event=\'file_uploaded\' '
                                                        . '            AND created>NOW() - INTERVAL \'31\' DAY '
                                                        . '            AND size > ' . $minSz . ' '
                                                        . '      GROUP BY Date) as speed on days.date=speed.date '
                                                        . ' ORDER BY days.date'
                                                        . ' ON DUPLICATE KEY UPDATE speed=speed.speed, enspeed = speed.enspeed, count = speed.count, encount = speed.encount ';
        }
        
        if( $dbtype == 'pgsql' ) {        
            $sql = '' 
                 . ' MERGE INTO ' . self::getDBTable() . ' ug  '
                                        . ' USING ( '
                                        . '   SELECT days.date as date, speed.speed as speed, speed.enspeed as enspeed, speed.count as count, speed.encount as encount '
                                        . '    FROM (SELECT (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
                                        . DBLayer::toIntervalDays("a+b") . ' date '
                                                 . '        FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 '
                                                 . '                     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 ) d, '
                                                 . '             (SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) m '
                                                 . '        WHERE (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
                                                 . DBLayer::toIntervalDays("a+b") . ' <= (select date(now())) '
                                                          . '        ORDER BY a + b) as days LEFT '
                                                          . ' JOIN (SELECT DATE(created) as date, '
                                                           .($encO ? '0 as speed, ' : '   AVG(case WHEN time_taken > 0 AND ' . $encF . ' THEN size/time_taken ELSE null END) as speed, ' )
                                                          . '   AVG(case WHEN time_taken > 0 AND ' . $encT . ' THEN size/time_taken ELSE null END) as enspeed, '
                                                           .($encO ? '0 as count, ' : '   AVG(case WHEN ' . $encF . ' THEN id ELSE null END) as count, ' )
                                                          . '   AVG(case WHEN ' . $encT . ' THEN id ELSE null END) as encount '
                                                          . '       from StatLogs '
                                                          . '      WHERE event=\'file_uploaded\' '
                                                          . '            AND created>NOW() - INTERVAL \'31\' DAY '
                                                          . '            AND size > ' . $minSz . ' '
                                                          . '      GROUP BY Date) as speed on days.date=speed.date '
                                                          . ' ORDER BY days.date ) as newdata '
                                                          . 'ON ug.date = newdata.date '
                                                          . ' WHEN MATCHED THEN ' 
                                                          . '    UPDATE SET speed = newdata.speed, enspeed = newdata.enspeed '
                                                          . ' WHEN NOT MATCHED THEN '
                                                          . '    INSERT (date,speed,enspeed) '
                                                          . '       VALUES (newdata.date,newdata.speed,newdata.enspeed) ';
        }                 

        
        $statement = DBI::prepare($sql);
        $statement->execute(array());

        
    }
    
    public static function cleanup()
    {
    }

    
}

    
