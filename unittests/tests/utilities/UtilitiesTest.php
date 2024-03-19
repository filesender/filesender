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
 * Utilities class test
 * 
 * @backupGlobals disabled
 */
class UtilitiesTest extends CommonUnitTestCase {

    /**
     * Init variables, first function called
     */
    protected function setUp(): void
    {
        echo "UtilitiesTest@ " . date("Y-m-d H:i:s") . "\n\n";
    }

    /**
     * Function to test Utilities::formatDate($timestamp) 
     * @see Utilities::formatDate($timestamp) 
     * 
     * @return boolean
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function testFormatDate() {
        $strDate = "2014-09-04";
        $timestamp = strtotime($strDate);

        try {
            $this->assertTrue(Utilities::formatDate($timestamp) == "04 Sep 2014");

            $this->displayInfo(get_class($this), __FUNCTION__, '');
        } catch (PHPUnit_Framework_AssertionFailedError $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }

        return true;
    }

    /**
     * Function used to test Utilities::sizeToBytes($size)
     * @see Utilities::sizeToBytes($size)
     * 
     * @return boolean
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function testSizeToBytes() {
        $size = "44P";

        try {
            $this->assertFalse(Utilities::sizeToBytes($size) <= 0);

            $size = "44T";
            $this->assertTrue(Utilities::sizeToBytes($size) == "48378511622144");

            $size = "54G";
            $this->assertTrue(Utilities::sizeToBytes($size) == "57982058496");

            $size = "13M";
            $this->assertTrue(Utilities::sizeToBytes($size) == "13631488");

            $size = "9K";
            $this->assertTrue(Utilities::sizeToBytes($size) == "9216");

            $this->displayInfo(get_class($this), __FUNCTION__, '');
        } catch (PHPUnit_Framework_AssertionFailedError $ex) {
            $this->displayError(get_class($this), __FUNCTION__, $ex->getMessage());
            throw new PHPUnit_Framework_AssertionFailedError();
        }
        return true;
    }

}
