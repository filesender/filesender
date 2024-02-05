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
class AuditlogTest extends CommonUnitTestCase {
    /*
     * Some variables used in tests case
     */

    private $transferSubject;    // Default subject of tranfer
    private $transferMessage;    // Default message of transfer
    private $recipient1;         // Recipient 1 for the transfer
    private $recipient2;         // Recipient 2 for the transfer

    /**
     * Init variables, first function called
     */

    protected function setUp(): void
    {
        echo "AuditlogTest@ " . date("Y-m-d H:i:s") . "\n\n";

        $configs = Config::get('auditlog_*');

        echo "Auditlog config data: " . print_r($configs, true) . "\n";

        if (!isset($configs['enable']) || !$configs['enable']) {
            throw new AuditLogNotEnabledException('Cannot test auditlog if not enabled in config.php');
        } else {
            $this->transfertSubject = "Subject test";
            $this->transfertMessage = "Message test";
            $this->fileName = "file01.txt";
            $this->fileSize = "100";
        }
    }

    /**
     * Function to create a Auditlog on database
     * 
     * @return int: transfer->id 
     */
    public function testCreate() {
        // Creating transfert object
        $currentDate = date('Y-m-d H:i:s');
        $transfer = Transfer::create(date('Y-m-d',  strtotime("+5 days")));
        $transfer->subject = $this->transfertSubject;
        $transfer->message = $this->transfertMessage;
        $transfer->save();

        Logger::logActivity(LogEventTypes::TRANSFER_START, $transfer);


        $res = AuditLog::all('created <= :created', array('created' => date('Y-m-d H:0:0',  strtotime($currentDate))));
        $this->assertTrue(sizeof($res) > 0);

        $timestamp = strtotime($currentDate) + 60*60;
        $res = StatLog::all('created <= :created', array('created' => date('Y-m-d H:0:0',  strtotime("+10 days"))));
        $this->assertTrue(sizeof($res) > 0);

        $this->assertNotNull($transfer->id);
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- AuditLog created');

        return $transfer->id;
    }

    /**
     * Function to test getting Auditlog from cache
     * 
     * @depends testCreate
     * 
     * @return int: transfer->id if test succeed
     */
    public function testRead($transferId) {

        $transfer = Transfer::fromId($transferId);

        $this->assertTrue($transfer->id > 0);

        $auditLog = AuditLog::fromTarget($transfer);

        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->id > 0);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- AuditLog got:'.$auditLog->id);

        return $transferId;
    }

    /**
     * Function to test deletion of Auditlog from deletion of transfer
     * 
     * @depends testRead
     * @return boolean: true if test succeed
     */
    public function testDeleteFromTransfer($transferId) {
        $this->assertTrue($transferId > 0);

        $transfer = Transfer::fromId($transferId);
        $this->assertNotNull($transfer);
        $this->assertTrue($transfer->id > 0 );

        $this->assertNotNull(AuditLog::fromTarget($transfer));
        $transfer->close();
        $this->assertNull(AuditLog::fromTarget($transfer));
        
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- AuditLog deleted from transfer:'.$transferId);

        return true;
    }

}
