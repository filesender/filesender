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
class CronTest extends CommonUnitTestCase {
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
    }


    /**
     * 
     * 
     * @return int: true test succeed
     */
    public function testCron() {

        $f = File::fromUid('23e81e38-7796-4af5-b63f-c4e3626166a9');
        $t = $f->transfer;
        $this->assertEquals( 2, $t->id );
        $this->assertEquals( 'closed',  $t->status );

        $f = File::fromUid('6fa18c0f-46fb-44ec-96c1-783525e6c5e7');
        $t = $f->transfer;
        $this->assertEquals( 5, $t->id );
        $this->assertEquals( 'available',  $t->status );

        
        return true;
    }
    
    
}
