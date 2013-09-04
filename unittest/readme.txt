Unit Testing Scripts
--------------------

Created by Chris Richter
September 2013

---------------------

Test scripts require Simpletest for PHP

Installation
---------------------
Download simpletest from http://www.simpletest.org/

Extract /simpletest into filesender root folder

/filesender/
/filesender/simpletest/
/filesender/www/

The test files can be downloaded form filesender.org in the branch of the filesender version you are testing
/filesender/unittest/

Set default configuration options for tests in utconfig.php
Note: Set default email accounts before running any email related tests.

To run all tests, open /filesender/unittest/index.php in a browser.

To run individual tests.
filesender/unittest/configUT.php
filesender/unittest/coreUT.php
filesender/unittest/dbqueriesUT.php
filesender/unittest/emailUT.php
filesender/unittest/fileUT.php
filesender/unittest/uploadUT.php
filesender/unittest/uploadUT.php
filesender/unittest/configUT.php
filesender/unittest/voucherUT.php



