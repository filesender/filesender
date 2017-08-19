<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2017, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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

require_once dirname(__FILE__) . '/../common/CommonUnitTestCase.php';

/**
 * Transfer class test
 * 
 * @backupGlobals disabled
 */
class DatasetTest extends CommonUnitTestCase {
    /*
     * Some variables used in tests case
     */
    private $test1;    // test1

    /**
     * Init variables, first function called
     */

    protected function setUp() {
        echo "DatasetTest@ " . date("Y-m-d H:i:s") . "\n\n";
        Config::localOverride('db_database','filesenderdataset' );

        $this->test1 = 4;
    }

    /**
     * Really simple database test
     * 
     * @return
     */
    public function testDatasetSimple() {

        $this->assertNotNull($this->test1);
        $this->assertTrue($this->test1 > 0);

        try {
            $statement = DBI::prepare('SELECT 1 as c;');
            $statement->execute(array());
            $data = $statement->fetch();
            $this->assertTrue($data['c'] == 1);
            
        } catch (Exception $ex) {
            $this->displayError(get_class(), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        $this->displayInfo(get_class(), __FUNCTION__, ' -- test1 created:' . $this->test1);

        return true;
    }

    /**
     * Function to test that there are the right number of users and guests
     * 
     * @depends testDatasetSimple
     * 
     * @return int: true test succeed
     */
    public function testDatasetUserAndGuestCount() {

        $userCount = 0;
        $guestCount = 0;

        try {
            $statement = DBI::prepare('select count(*) as c from UserPreferences;');
            $statement->execute(array());
            $data = $statement->fetch();
            $userCount = $data['c'];

            $statement = DBI::prepare('select count(*) as c from Guests;');
            $statement->execute(array());
            $data = $statement->fetch();
            $guestCount = $data['c'];
            
            $this->assertTrue($userCount  > 10000);
            $this->assertTrue($guestCount >  3000);
            
        } catch (Exception $ex) {
            $this->displayError(get_class(), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        $this->displayInfo(get_class(), __FUNCTION__, ' -- userCount: $userCount' );

        return true;
    }

    /**
     * Function to switch to a created user in the synth dataset
     * 
     * @depends testDatasetUserAndGuestCount
     * 
     * @return int: true test succeed
     */
    public function testDatasetDefaultUser() {

        $email = '';
        
        try {
            $cred->forceCredentialsToDefaultUser();
            $user = Auth::user();
            $email = $user['email'];
            $this->assertTrue($email == 'testdriver@localhost.localdomain');
            
        } catch (Exception $ex) {
            $this->displayError(get_class(), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        $this->displayInfo(get_class(), __FUNCTION__, ' -- email: $email' );
        
        return true;
    }


    /**
     * 
     * @depends testDatasetSimple
     * 
     * @return int: true test succeed
     */
    public function testDatasetTransactionCount() {

        $userCount = 0;
        $guestCount = 0;

        try {
            $statement = DBI::prepare("select count(*) as c,translation_id "
                                    . " from  TranslatableEmails "
                                    . " where translation_id = 'transfer_available' "
                                    . " group by translation_id;");
            $statement->execute(array());
            $data = $statement->fetch();
            $count = $data['c'];

            $this->assertTrue($count  > 60000);
            
        } catch (Exception $ex) {
            $this->displayError(get_class(), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        $this->displayInfo(get_class(), __FUNCTION__, ' -- count: $count' );

        return true;
    }

    
    
}
