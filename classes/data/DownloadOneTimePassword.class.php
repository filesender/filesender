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
 * allow the server to challenge a recipient with a one time code to their
 * email address and verify that the user can produce that code.
 */
class DownloadOneTimePassword extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        // transaction id
        'tid' => array(
            'type' => 'uint',
            'size' => 'big',
            'primary' => true
        ),
        // recipient id 
        'rid' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true
        ),
        // When did this happen
        'created' => array(
            'type' => 'datetime',
            'primary' => true
        ),
        // password sent to the user
        'password' => array(
            'type' => 'string',
            'size' => 64,
            'null' => false,
        ),
        // When did the code get verified
        'verified' => array(
            'type' => 'datetime',
            'null' => true,
        )
    );

    protected static $secondaryIndexMap = array();

    public static function getViewMap()
    {
        $constable = DBConstantAVProgram::getDBTable();
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select '.self::getDBTable().'.* '
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable();
            ;
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Set selectors
     */
    const FOR_TRANSFER_RECIPIENT = ' tid = :tid and rid = :rid  ORDER BY created DESC';
    const FOR_TRANSFER_RECIPIENT_CREATED = ' tid = :tid and rid = :rid and created = :created ORDER BY created DESC';
    
    /**
     * Properties
     */
    protected $tid = null;
    protected $rid = null;
    protected $created = null;
    protected $password = null;
    protected $verified = null;
    
    /**
     * Constructor
     *
     * @param integer $tid transfer  id
     * @param integer $rid recipient id
     *
     * @throws 
     */
    protected function __construct($tid = null, $rid = null, $pass = null)
    {
    }
    
    /**
     * Create a new one time password to allow the recipient to download this transfer
     *
     *
     * @return AVResult
     */
    public static function create( Transfer $transfer, Recipient $recipient, $pass )
    {
        $ret = new self();

        $ret->tid = $transfer->id;
        $ret->rid = $recipient->id;
        $ret->created = time();
        $ret->password = $pass;
        
        $ret->insertRecord($ret->toDBData());
        return $ret;
    }

    public static function mostRecentForDownload( Transfer $transfer, Recipient $recipient )
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE '.self::FOR_TRANSFER_RECIPIENT);
        $statement->execute(
            array(
                ':tid' => $transfer->id,
                ':rid' => $recipient->id,
            )
        );
        $data = $statement->fetch();
        if (!$data) {
            throw new RecipientNotFoundException('tid = '.$transfer->id);
        }

        $ret = new self();
        $ret->fillFromDBData($data);
        return $ret;
    }
    
    
    /**
     * Save in database
     */
    public function save()
    {
        $this->updateRecord($this->toDBData(), array('tid','rid','created'));
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
            'tid',
            'rid',
            'created',
            'password',
            'verified',
         ))) {
            return $this->$property;
        }
        throw new PropertyAccessException($this, $property);
    }

    public function __set($property, $value)
    {
        if ($property == 'verified') {
            $this->verified = $value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }

    
    /**
     * Get one time password for the transfer / recipient
     *
     */
    public static function forTransferAndReceipient( Transfer $transfer, Recipient $recipient, $created )
    {
        $ret = self::all(FOR_TRANSFER_RECIPIENT_CREATED,
                         array(
                             'tid' => $transfer->id,
                             'rid' => $recipient->id,
                             'created' => $created,
                         )
        );
        return $ret;
    }
    
    

    public static function cleanup()
    {
        $dbtype = Config::get('db_type');

        // trim entries that are older than a day
        // and the valid duration (in case it is over a day)
        $trimcreated = time();
        $trimcreated -= 24*3600;
        $trimcreated -= Config::get('download_verification_code_valid_duration');

        $statement = DBI::prepare("delete from ".self::getDBTable()." where created < :trimcreated " );
        $statement->execute(array(':trimcreated' => date('Y-m-d H:i:s', $trimcreated)));
        
    }

    public function isCodeReTooOld()
    {
        $otpsec = time() - $this->created;
        if( $otpsec > Config::get('download_verification_code_valid_duration')) {
            return true;
        }
        return false;
    }

}
