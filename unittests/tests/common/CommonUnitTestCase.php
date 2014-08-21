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


/**
 * Common unit test case file
 */
abstract class CommonUnitTestCase extends PHPUnit_Framework_TestCase{
    
    
    /**
    * Display error on test on stdout
    *
    * @param $class : name of the test class  
    * @param $test : name of the test
    * @param $message : message result from exception
    */
   public function displayError($class, $test, $message){
      print_r("\n---------------------------------------------------------------------------------------\n");
      print_r("Result test : [KO]\n\n");
      print_r($class."::".$test."\t\t");
      print_r("\n\tReason:\t".$message);
      print_r("\n---------------------------------------------------------------------------------------\n");
   }

   /**
    * Display info about a test on stdout
    *
    * @param $class : name of the test class 
    * @param $test : name of the test
    * @param $message : additional message to show
    */
   public function displayInfo($class, $test, $message){
      print_r("Result test : [OK]\n\n");
      print_r(get_class()."::".$test."\t\t");
      if ($message != ""){
         print_r("\n\Message:\t".$message);
      }
      print_r("\n---------------------------------------------------------------------------------------\n");
   }
  
}
    