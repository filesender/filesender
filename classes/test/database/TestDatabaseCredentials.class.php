<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 * Credential handling.
 *
 * Call forceCredentialsToDefaultUser() to auth as a default user
 * Create users with createUsers(), create guests with createGuests().
 *
 * switchToUser( number ) can be used to switch to a user createed by createUsers().
 */
class TestDatabaseCredentials
{
    protected $creator = null;
    
    /**
     * @param $creator is the TestDatabaseCreator object. Use
     * TestDatabaseCreator::getTestDatabaseCredentials() to get an object of this class.
     *
     */
    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    public function output($msg)
    {
        $this->creator->output($msg);
    }
    
    /**
     * force the current user credentials to the uid/email given
     * this might also create some records in the database to track this user.
     * after this call Auth::user() will return some data reflecting the setting.
     *
     * @param string uid
     * @param string email
     * @param string name
     *
     * @see forceCredentialsToDefaultUser()
     */
    public function forceCredentialsToUser($uid, $email, $name = null)
    {
        Auth::testingForceToUser($uid, $email, $name);
        // $this->output('switched to user ' . $uid );
    }

    /**
     * Move to being authenticated by the primary test user
     */
    public function forceCredentialsToDefaultUser()
    {
        $this->forceCredentialsToUser(
            'filesender-testdriver@localhost.localdomain',
                                       'filesender-testdriver@localhost.localdomain',
                                       'testdriver'
        );
    }

    /**
     * Since a few functions want to be able to get the email
     *  and username for a user with an id number this function
     * handles how that data is structured
     */
    public function getUserNameAndEmail($id)
    {
        $name  = 'tester' . $id;
        $email = $name . '@localhost.localdomain';
        return array( $name, $email );
    }
    
    /**
     * After calling createUsers() you can select a user by number and
     * switch to that user as your credentials
     *
     */
    public function switchToUser($id)
    {
        list($name, $email) = $this->getUserNameAndEmail($id);
        $this->forceCredentialsToUser($email, $email, $name);
    }
    
    /**
     * Create a number of users in the system and then switch to the default
     * user.
     *
     * @param int num the number of users to create
     */
    public function createUsers($num = 100)
    {
        $this->output("creating $num users...");
        for ($i = 0; $i < $num; $i++) {
            list($name, $email) = $this->getUserNameAndEmail($i);
            $this->forceCredentialsToUser($email, $email, $name);
        }
        
        $this->forceCredentialsToDefaultUser();
    }

    private $email_guest_created_receipt_value = 0;
    public function createGuests($num = 100)
    {
        $this->output("creating $num guests...");
        for ($i = 0; $i < $num; $i++) {
            $recipient = 'guest' . $i . '@localhost.localdomain';
            $g = Guest::create($recipient);
            $this->email_guest_created_receipt_value++;

            // only half guest creates will send a receeipt
            $g->options = array( 'can_only_send_to_me' => false,
                                 'email_upload_started' => true,
                                 'email_guest_created_receipt' =>
                                     !($this->email_guest_created_receipt_value % 2) );
            $g->makeAvailable();
        }
    }
}
