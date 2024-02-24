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
    private $creator = null;
    private $cred    = null;
    private $tc      = null;

    /**
     * Init variables, first function called
     */

    protected function setUp(): void
    {
        if (Config::get('db_type') == 'mysql' ) {
            $this->markTestSkipped(
                'The dataset test only runs on pgsql at the moment.'
            );
        }        
        echo "DatasetTest@ " . date("Y-m-d H:i:s") . "\n\n";
        Config::localOverride('db_database','filesenderdataset' );

        $this->test1 = 4;
        $this->creator  = new TestDatabaseCreator();
        $this->cred     = $this->creator->getTestDatabaseCredentials();
        $this->tc       = $this->creator->getTestDatabaseTransfers();

        echo "current db_database is " . Config::get('db_database') . "\n";
        DBI::forceReconnect();
    }

    /**
     * Really simple database test
     * 
     * @return
     */
    public function testDatasetDBName() {

        $dbname = 'unknown';
        
        try {
            $statement = DBI::prepare('select current_database() as c;');
            $statement->execute(array());
            $data = $statement->fetch();
            $dbname = $data['c'];
            
            $this->displayInfo(get_class($this), __FUNCTION__, ' -- dbname:' . $dbname);
            $this->assertTrue( $dbname == 'filesenderdataset' );
            
        } catch (Exception $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        return true;
    }
    
    /**
     * Really simple database test
     * 
     * @depends testDatasetDBName
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
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- test1 created:' . $this->test1);

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
            $this->displayInfo(get_class($this), __FUNCTION__, " -- userCount: $userCount  guestCount: $guestCount " );
            
            $this->assertTrue($userCount  >  10000);
            $this->assertTrue($guestCount >=  3000);
            
        } catch (Exception $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        $this->displayInfo(get_class($this), __FUNCTION__, " -- userCount: $userCount" );

        return true;
    }

    /**
     * Function to switch to a created user in the synth dataset
     * 
     * @depends testDatasetSimple
     * 
     * @return int: true test succeed
     */
    public function testDatasetDefaultUser() {

        $user_id = '';
        
        try {
            $this->cred->forceCredentialsToDefaultUser();
            $user = Auth::user();
            $user_id = $user->saml_user_identification_uid;
            $this->displayInfo(get_class($this), __FUNCTION__, " -- user_id: $user_id" );
            $this->assertTrue($user_id == 'filesender-testdriver@localhost.localdomain');
            
        } catch (Exception $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        $this->displayInfo(get_class($this), __FUNCTION__, " -- user_id: $user_id" );
        
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
            $this->displayInfo(get_class($this), __FUNCTION__, " -- count: $count" );

            $this->assertTrue($count  > 60000);
            
        } catch (Exception $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        
        return true;
    }

    
    /**
     * 
     * @depends testDatasetSimple
     * 
     * @return int: true test succeed
     */
    public function testDatasetTransactionInspection() {

        $a = 1;
        $f = File::fromUid('7f123114-0ad2-d937-5bc6-f136695877cd');
        $t = $f->transfer;
        $a = $t->id;

        $this->assertEquals( 35518,    $t->id     );
        $this->assertEquals( 'closed', $t->status );
        
        $this->displayInfo(get_class($this), __FUNCTION__, " -- a: $a" );
        
        return true;        
    }

    /**
     * 
     * @depends testDatasetTransactionInspection
     * 
     * @return int: true test succeed
     */
    public function testDatasetTransactionInspectionOpen() {

        $a = 1;
        $f = File::fromUid('dadf8767-07ee-eafa-2e38-3fbc70945bbd');
        $t = $f->transfer;
        $a = $t->id;

        $this->assertEquals( 35517,        $t->id     );
        $this->assertEquals( 'available',  $t->status );

        $alog = $t->auditlogs;
        $this->assertEquals( 22,  count($alog)    );
        $this->assertEquals( 'testdriver@localhost.localdomain', $alog[0]->author_id );
        $this->assertEquals( 'testdriver@localhost.localdomain', $alog[1]->author_id );
        $this->assertEquals( 'testdriver@localhost.localdomain', $alog[2]->author_id );
        $this->assertEquals( 'testdriver@localhost.localdomain', $alog[3]->author_id );

        $this->assertEquals( 'file_uploaded', $alog[1]->event );
        $this->assertEquals( 'File',          $alog[1]->target_type );
        $this->assertEquals( 45386,           $alog[1]->target_id );

        $this->assertEquals( 'transfer_available', $alog[12]->event );
        $this->assertEquals( 'Transfer'          , $alog[12]->target_type );
        $this->assertEquals( 35517               , $alog[12]->target_id );
        
        $this->displayInfo(get_class($this), __FUNCTION__, " -- a: $a" );        
        return true;
    }


    /**
     * 
     * @depends testDatasetTransactionInspectionOpen
     * 
     * @return int: true test succeed
     */
    public function testDatasetTranslatableEmails() {
        $a = 1;
        $f = File::fromUid('dadf8767-07ee-eafa-2e38-3fbc70945bbd');
        $t = $f->transfer;

        $e = TranslatableEmail::fromContext($t);
        $uploadcomplete = $e[171545];
        $travail = $e[171546];
        
        $this->assertEquals( 'Transfer',        $uploadcomplete->context_type );
        $this->assertEquals( $t->id,            $uploadcomplete->context_id   );
        $this->assertEquals( 'upload_complete', $uploadcomplete->translation_id );
        
        $this->assertEquals( 'Transfer',        $travail->context_type );
        $this->assertEquals( $t->id,            $travail->context_id   );
        $this->assertEquals( 'transfer_available', $travail->translation_id );
        
        
        $this->displayInfo(get_class($this), __FUNCTION__, " -- a: $a" );        
        return true;
    }


    /**
     * 
     * @depends testDatasetTranslatableEmails
     * 
     * @return int: true test succeed
     */
    public function testDatasetRecipients() {
        $a = 1;
        $f = File::fromUid('dadf8767-07ee-eafa-2e38-3fbc70945bbd');
        $t = $f->transfer;
        $r = $t->recipients;

        $r1 = $r[64485];
        $r2 = $r[64486];

        $this->assertEquals( 'tester@localhost.localdomain',         $r1->email );
        $this->assertEquals( 'afc198dd-606a-2e9a-191d-62c3fee9729d', $r1->token );
        $this->assertEquals( $t->id, $r1->transfer_id );

        $this->assertEquals( 'tester2@localhost.localdomain',         $r2->email );
        $this->assertEquals( '02dd46d6-ebcc-c597-3b9a-0b3e228881ba',  $r2->token );
        $this->assertEquals( $t->id, $r2->transfer_id );
        
        
        $this->displayInfo(get_class($this), __FUNCTION__, " -- a: $a" );        
        return true;
    }
    
    
}
