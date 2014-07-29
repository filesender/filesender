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

//Require environment (fatal)
if (!defined('FILESENDER_BASE')) die('Missing environment');

// Use database object superclass
require_once FILESENDER_BASE.'/classes/new/DBObject.class.php';

//check for static implementation - development environment only
if (file_exists('AuthSaml.static.php') && file_exists('AuthVoucher.static.php')) {
    include_once 'AuthSaml.static.php';
    include_once 'AuthVoucher.static.php';
} else {
    include_once 'AuthSaml.class.php';
    include_once 'AuthVoucher.class.php';
}


/** 
 * NOT a user class for storing users.
 * Instead, it represents the current user for authentication
 * and for storing user preferences records in the Users table 
 * ( display language, upload opts, whether user has previously accepted the AuP, which options invitees by voucher will see
 * -- data is stored only on a successful file transfer
 */
class User extends DBObject
{
    protected static $dataMap = array(
        'uid' => array(
            'type' => 'string',
            'size' => 60,
            'primary' => true,
        ),
        'organization' => array(
            'type' => 'string',
            'size' => 80,
        ),
        'aup_ticked' => array(
            'type' => 'bool',
            'size' => 1,
        ),
        'aup_last_ticked_date' => array(
            'type' => 'date',
        ),
        'file_preferences' => array(
            'type' => 'string',
            'size' => 250,       //to hold about three JSON-enc lines
        ),
        'pref_lang' => array(
            'type' => 'string',
            'size' => 4,
        ),
        /*'voucher_preferences' => array(
            'type' => 'string',
            'size' => 250,
        ),*/
    );
    /**
     * Properties
     */
    protected static $instance = null;	//there's only one user sending something concurrently
    
    protected $recipients = array(); // to save recipients in an "address book" for quick e-mail adding?
    protected $uid = null;
    protected $organization = null;
    protected $aup_ticked = false;
    protected $aup_last_ticked_date = null; //string rep of date
    protected $file_preferences = null;
    protected $isauth = false;
    protected $isadmin = false;
    protected $userdata = null;
    protected $useremail = null;
    protected $pref_lang = null;
    protected $properties = array();

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Constructor
     * @param string $uid: identifier of DBO (User, File, etc) to load from database. 
     * null if DBO doesn't exist (has never used - made a successful file
     * transfer - FS)
     * @param array $data: data to create the transaction from (if already
     * fetched from DB)
	 *
     * @throws DBObjectNotFoundException
     */
    public function __construct($uid = null, $data = null)
    {
        parent::__construct($uid = null, $data = null);
        $this->$properties = array('recipients', 'uid', 'organization',
        'aup_ticked', 'aup_last_ticked_date', 'file_preferences', 'isauth',
        'isadmin', 'userdata', 'useremail', 'pref_lang');

    	$isauth = AuthSaml::isAuth();
	if ($isauth) {
	    $this->isadmin = AuthSaml::authIsAdmin();
	    $userdata = AuthSaml::sAuth();

	    if (!$userdata == 'err_attributes') {
		$useremail = $userdata['email'];
	    }
		
	    $this->isauth = $isauth;
	    $this->isadmin = $isadmin;
	    $this->userdata = $userdata;
	}
    }

    /**
     *  This method is really for setting user preferences
     *  and preferred language. After fetching needed data from browser/input
     *  it can use magic setter
     *  TODO: finish implementing and (maybe) rename
     *  
     *  @overrides DBObject::create()
     *  @param $lang: preferred language as string
     */
    public static function create($lang)
    {
        $this->pref_lang = $lang;
    }

    /**
     * Getter
     * 
     * @param string $property: property to get
     * @throws PropertyAccessException
     * @return property value
     */
    public function __get($property)
    {
        if (in_array($property, $this->properties ))
            return $this->property;
        throw new PropertyAccessException($this, $property);
    }

    /**
     * Setter
     * 
     * @param string $property: property to get
     * @param mixed $value: value to set property to
     */
    public function __set($property, $value)
    {
        if (in_array($property, $this->properties)) {
            $this->property = $value;
        }
        else
            throw PropertyAccessException($this, $property);
    }
    
    /*
     * To store the user preferences object in the database when the transfer
     * has been submitted
     */
    public function save()
    {
        $data = $this->toDBData();
    }
}
