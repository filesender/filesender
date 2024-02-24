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
 * Database class test
 * 
 * @backupGlobals disabled
 */
class DBsTest extends CommonUnitTestCase {

    protected $db;

    /**
     * Init variables, first function called
     */
    protected function setUp(): void
    {
        echo "DBsTest@ " . date("Y-m-d H:i:s") . "\n\n";
    }

    /**
     * Funciton used to test database connexion
     * 
     * @return boolean: true if test succeed
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function testConnexion() {
        try {
            $statement = DBI::prepare('SELECT 1;');
            $statement->execute(array());
            $data = $statement->fetch();
            $this->assertTrue(isset($data['?column?']) ? $data['?column?'] == 1 : $data[1] == 1);

            $this->displayInfo(get_class($this), __FUNCTION__, 'Connexion DB OK');
        } catch (Exception $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        return true;
    }

    /**
     * Test the Insert/Update "Upsert" class
     */
/*
    public function testUpsert() {

// Have to find minimal version of pgsql that can use "ON keyword"
 
        $tableName = 'DBTestingTableStringNumbers';

        DatabaseUpsert::upsert( 
            "insert into $tableName (id,data,created) values (1,'first',now())",
            "id",
            "id = 1, data = 'first', created = now()" );


        DatabaseUpsert::upsert( 
            "insert into $tableName (id,data,created) values (1,'second',now())",
            "id",
            "id = 1, data = 'second', created = now()" );
        
    }
*/        
    
}
