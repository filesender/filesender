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
 * Report class test
 * 
 * @backupGlobals disabled
 */
class ReportTest extends CommonUnitTestCase {

    private $transferSubject;    // Default subject of tranfer
    private $transferMessage;    // Default message of transfer

    /**
     * Init variables, first function called
     * 
     */

    protected function setUp(): void
    {
        echo "ReportTest@ " . date("Y-m-d H:i:s") . "\n\n";

        $this->transferSubject = "Subject test";
        $this->transferMessage = "Message test";
    }

    /**
     * Function to test creation of repport
     * 
     * @return type
     */
    public function testCreate() {
        // Creating transfert object
        $currentDate = date('Y-m-d H:i:s');
        $transfer = Transfer::create(date('Y-m-d',  strtotime("+5 days")));
        $transfer->subject = $this->transferSubject;
        $transfer->message = $this->transferMessage;
        $transfer->save();

        $this->assertNotNull($transfer->id);
        $this->assertTrue($transfer->id > 0);

        AuditLog::create(LogEventTypes::TRANSFER_START, $transfer);

        $report = new Report(ReportTypes::STANDARD, $transfer);
        $results = $report->generateReport();

        $this->assertTrue($results['reports'] != "");

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- Report generated');

        return $transfer->id;
    }

}
