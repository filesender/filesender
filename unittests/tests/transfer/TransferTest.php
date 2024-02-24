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
 * Transfer class test
 * 
 * @backupGlobals disabled
 */
class TransferTest extends CommonUnitTestCase {
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
        echo "TransferTest@ " . date("Y-m-d H:i:s") . "\n\n";

        $this->transferSubject = "Subject test FOR CRON";
        $this->transferMessage = "Message test FOR CRON";

        $this->recipient1 = "emiel@codebridge.nl";
        $this->recipient2 = "emiel@codebridge.nl";
    }

    /**
     * Function to create a transfer on database
     * 
     * @return Transfer: the transfer created in database
     */
    private function create() {
        $transfer = Transfer::create(date('Y-m-d',  strtotime("+5 days")));
        echo "create() user()::id " . Auth::user()->id . "\n";
        echo "create() tr->userid " . $transfer->userid . "\n";
        $transfer->subject = $this->transferSubject;
        $transfer->message = $this->transferMessage;
        $transfer->save();

        $transfer->addRecipient($this->recipient1);
        $transfer->addRecipient($this->recipient2);

        return $transfer;
    }

    /**
     * Function used to test creation of transfer in database
     * 
     * @return int: transfer->id if test succeed
     */
    public function testCreate() {
        // Creating transfert object
        $transfer = $this->create();

        $this->assertNotNull($transfer->id);
        $this->assertTrue($transfer->id > 0);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- Transfer created:' . $transfer->id);

        return $transfer->id;
    }

    /**
     * Function to test getting transfer from cache
     * 
     * @depends testCreate
     * 
     * @return int: transfer->id if test succeed
     */
    public function testRead($transferId) {

        $transfer = Transfer::fromId($transferId);

        $this->assertTrue($transfer->id > 0);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- Transfer got:' . $transfer->id);

        return $transferId;
    }

    /**
     * Function to test deletion from database of the transfer
     * 
     * @depends testRead
     * @return boolean: true if test succeed
     */
    public function testDelete($transferId) {
        $this->assertTrue($transferId > 0);

        $transfer = Transfer::fromId($transferId);

        $this->assertNotNull($transfer);

        $transfer->close(true);

        DBObject::purgeCache($transfer->getClassName());

        $isDeleted = false;
        try {
            Transfer::fromId($transferId);
        } catch (TransferNotFoundException $e) {
            $this->displayInfo(get_class($this), __FUNCTION__, '');
            $isDeleted = true;
        }

        $this->assertTrue($isDeleted);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- Transfer deleted:' . $transferId);

        return $isDeleted;
    }

    /**
     * Function to test deletion from CRON task
     * 
     * @depends testDelete
     * @return boolean: true if test succeed
     */
    public function testDeleteFromCron() {
        $transferId = $this->create()->id;

        $this->assertTrue($transferId > 0);

        $transfer = Transfer::fromId($transferId);
        $this->assertNotNull($transfer);

        $statement = DBI::prepare("UPDATE " . $transfer->getDBTable() . " SET expires = :expire WHERE id = :id ");
        $statement->execute(array('expire' => date('Y-m-d', strtotime("-1 days")), 'id' => $transfer->id));

        DBObject::purgeCache($transfer->getClassName());

        $isDeleted = false;
        try {
            $t = Transfer::fromId($transferId);
            if ($t->isExpired()) $isDeleted = true;
        } catch (TransferNotFoundException $e) {
            $this->displayInfo(get_class($this), __FUNCTION__, ' -- Transfer deleted:' . $transferId);
            $isDeleted = true;
        }

        $this->assertTrue($isDeleted);

        return $isDeleted;
    }

}
