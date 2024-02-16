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
 * Auditlog class test
 * 
 * @backupGlobals disabled
 */
class ClientlogTest extends CommonUnitTestCase {
    /*
     * Some variables used in tests case
     */
    
    const CREATE = 5;
    
    /**
     * Init variables, first function called
     */

    public static function setUpBeforeClass(): void
    {
        echo "ClientlogTest@ " . date("Y-m-d H:i:s") . "\n\n";

        echo "Client logs stash size: " . ClientLog::stashSize() . "\n";
        
        if (!Auth::isAuthenticated(false))
            throw new AuthAuthenticationNotFoundException();
        
        // Ensure empty set
        DBI::exec('DELETE FROM '.ClientLog::getDBTable());
    }
    
    /**
     * Cleanup in any case
     */
    public static function tearDownAfterClass(): void
    {
        // Clean any trash
        DBI::exec('DELETE FROM '.ClientLog::getDBTable());
    }

    /**
     * Function to create a Clientlog on database
     * 
     * @return bool: if test succeeds
     */
    public function testCreate() {
        // Creating log objects
        $message = 'unique message '.uniqid();
        $log = ClientLog::create(Auth::user(), $message);
        $log->save();

        $this->assertNotNull($log->id);
        $this->assertEquals($message, $log->message);
        $this->assertEquals(Auth::user()->id, $log->userid);
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- ClientLog created');
        
        return true;
    }
    
    /**
     * Function to test getting ClientLog from cache
     * 
     * @depends testCreate
     * 
     * @return int: count of logs if test succeed
     */
    public function testRead() {
        $logs = ClientLog::fromUser(Auth::user());

        $this->assertNotNull($logs);
        $this->assertTrue(is_array($logs));
        $this->assertNotEmpty($logs);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- ClientLog got:'.count($logs));

        return count($logs);
    }
    
    /**
     * Function to test deletion of Clientlog
     *
     * @depends testRead
     *
     * @return boolean: true if test succeed
     */
    public function testDelete() {
        $logs = ClientLog::fromUser(Auth::user());
        
        array_shift($logs)->delete();
        
        $this->assertEmpty(ClientLog::fromUser(Auth::user()));
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- ClientLog deleted');
        
        return true;
    }
    
    /**
     * Function to test stashing of Clientlogs
     *
     * @depends testDelete
     *
     * @return boolean: true if test succeed
     */
    public function testStash() {
        ClientLog::stash(Auth::user(), array_fill(0, self::CREATE, 'message'));
        
        $logs = ClientLog::fromUser(Auth::user());
        $this->assertEquals(self::CREATE, count($logs));
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- '.self::CREATE.' ClientLog stashed');
        
        return true;
    }
    
    /**
     * Function to test over-stashing of Clientlogs
     *
     * @depends testStash
     *
     * @return boolean: true if test succeed
     */
    public function testOverStash() {
        $size = ClientLog::stashSize();
        
        ClientLog::stash(Auth::user(), array_fill(0, 2 * $size, 'message'));
        
        $logs = ClientLog::fromUser(Auth::user());
        $this->assertLessThanOrEqual($size, count($logs));
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- '.self::CREATE.' ClientLog stash size applied');
        
        return true;
    }
    
    /**
     * Function to test cleanup of Clientlogs
     *
     * @depends testOverStash
     *
     * @return boolean: true if test succeed
     */
    public function testClean() {
        // Update dates so clean works
        $days = Config::get('clientlogs_lifetime');
        if(!$days) $days = 10;
        $days += 2; // margin to ensure logs are old enough
        
        $st = DBI::prepare('UPDATE '.ClientLog::getDBTable().' SET created = :date');
        $st->execute(array(':date' => date('Y-m-d', time() - $days * 86400)));
        
        // Ensure update did its job
        $logs = ClientLog::fromUser(Auth::user());
        $log = array_shift($logs);
        $this->assertNotNull($log);
        $this->assertLessThanOrEqual(time() - $days * 86400, $log->created);
        
        // Actual cleanup
        ClientLog::clean();
        
        $this->assertEmpty(ClientLog::fromUser(Auth::user()));
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- ClientLog cleaned');
        
        return true;
    }
}
