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


require_once('CommonPHPUnitConfigs.php');

use PHPUnit\Framework\TestCase;

/**
 * Class containing DB operations constants
 */
class DBOperations {

    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';

}

/**
 * Class containing DB errors constants
 */
class DBErrors {

    const DB_ERROR_SYNTAX = -2;
    const DB_ERROR_CONSTRAINT = -3;
    const DB_ERROR_NOT_FOUND = -4;
    const DB_ERROR_ALREADY_EXISTS = -5;
    const DB_ERROR_UNSUPPORTED = -6;
    const DB_ERROR_MISMATCH = -7;
    const DB_ERROR_INVALID = -8;
    const DB_ERROR_NOT_CAPABLE = -9;
    const DB_ERROR_TRUNCATED = -10;
    const DB_ERROR_INVALID_NUMBER = -11;
    const DB_ERROR_INVALID_DATE = -12;
    const DB_ERROR_DIVZERO = -13;
    const DB_ERROR_NODBSELECTED = -14;
    const DB_ERROR_CANNOT_CREATE = -15;
    const DB_ERROR_CANNOT_DELETE = -16;
    const DB_ERROR_CANNOT_DROP = -17;
    const DB_ERROR_NOSUCHTABLE = -18;
    const DB_ERROR_NOSUCHFIELD = -19;
    const DB_ERROR_NEED_MORE_DATA = -20;
    const DB_ERROR_NOT_LOCKED = -21;
    const DB_ERROR_VALUE_COUNT_ON_ROW = -22;
    const DB_ERROR_INVALID_DSN = -23;
    const DB_ERROR_CONNECT_FAILED = -24;
    const DB_ERROR_EXTENSION_NOT_FOUND = -25;
    const DB_ERROR_NOSUCHDB = -26;
    const DB_ERROR_ACCESS_VIOLATION = -27;

}

/**
 * Abstract class containing common function to test CRUD operations on database
 */
abstract class CommonDatabaseTestCase extends TestCase {

    /**
     * Function used to test CREATE operation
     */
    abstract public function testCreate();

    /**
     * Function used to test READ  operation
     * 
     * @param int: id of object to be got from database
     */
    abstract public function testRead($id);

    /**
     * Function used to test UPDATE  operation
     * 
     * @param int: id of object to be updated from database
     */
    abstract public function testUpdate($id);

    /**
     * Function used to test DELETE  operation
     * 
     * @param $id: id of object to be deleted from database
     */
    abstract public function testDelete($id);

    /**
     * This is a do nothing method to make sure that this class has
     * at least one method with an assert for automated scanners.
     */
    public function testCommonDatabaseTestCaseNothingMethod() {
        $this->assertTrue( true );
    }
    
    /**
     * Display error on test on stdout
     *
     * @param $class : name of the test class  
     * @param $test : name of the test
     * @param $message : message result from exception
     */
    public function displayError($class, $test, $message) {
        print_r("\n---------------------------------------------------------------------------------------\n");
        print_r("Result test : [KO]\n\n");
        print_r($class . "::" . $test . "\t\t");
        print_r("\n\tReason:\t" . $message);
        print_r("\n---------------------------------------------------------------------------------------\n");
    }

    /**
     * Display info about a test on stdout
     *
     * @param $class : name of the test class 
     * @param $test : name of the test
     * @param $message : additional message to show
     */
    public function displayInfo($class, $test, $message) {
        print_r("Result test : [OK]\n\n");
        print_r(get_class($this) . "::" . $test . "\t\t");
        if ($message != "") {
            print_r("\n\Message:\t" . $message);
        }
        print_r("\n---------------------------------------------------------------------------------------\n");
    }

}
