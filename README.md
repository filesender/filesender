Updated: 6 February 2013

This is the README.txt for version 1.5 of the FileSender software.

FileSender is a software package that implements a web-based application that
allows authenticated users to securely and easily send arbitrarily large files
to other users. Authentication of users is provided through SAML2, LDAP and
RADIUS. Users without an account can be sent an upload voucher by an
authenticated user. FileSender is developed to the requirements of the higher
education and research community.

The purpose of the software is to send a large file to someone, have that file
available for download for a certain amount of time, and after that
automatically delete the file. The software is not intended as a permanent
file publishing platform.

All major workflow actions (such as files or vouchers becoming available, or
being deleted) are signalled to both sender as well as recipient by means of
html-templated emails

FileSender project home:
   http://www.filesender.org/

FileSender installation and configuration documentation is available at:
   https://www.assembla.com/spaces/file_sender/wiki/Documentation_v1-5

Support is available on a best-effort basis through the FileSender development
mailinglist filesender-dev@filesender.org. For information on how to subscribe
and the mailinglist archives:
   http://www.assembla.com/wiki/show/file_sender/Support_and_Mailinglists


Acknowledgements
================

The 1.5 release of the FileSender software was developed by an international
core team with many others contributing. The FileSender 1.5 core team members
were Chris Richter, Guido Aben, Jan Meijer, Wendy Mason, Xander Jansen

Many others have contributed to make FileSender 1.5 possible:
   https://www.assembla.com/spaces/file_sender/wiki/Acknowledgements_for_v1-5

Sustained funding, staff and other resources for the FileSender project are
provided by the National Research and Educational Networks (NRENs) AARNet
(Australia), HEAnet (Ireland), SURFnet (The Netherlands) and UNINETT (Norway).  

For the 1.5 release FileSender received targeted financial contributions from
ARNES (Slovenia), Belnet (Belgium), CSC/FUNET (Finland), CESNET (Czech
Republic), the Hebrew University of Jerusalem (Israel) and UNI-C (Denmark).


The FileSender project started in April 2009 and was initiated by AARNet,
HEAnet and UNINETT.



# Frontend tests
### Requirements
Requirements for use of the Selenium Tests

+ A SauceLabs. After is has been created a API key can be requested
+ A running installation of FileSender
+ Optional: When the installation is running locally or behind a NAT-solution, Sauce Connect needs to be set up first
+ A terminal window with PHP command line
+ Firefox: For writing tests more easily

###Requesting API key
Log in to saucelabs.com. Go to Dashboard, and click on your name that you have filled in during sign-up in the bottom right corner. Click User Settings. Under Access Key click Show to see your access key. Copy and paste it into an empty text file. We'll be needing this later.

###Setting up Sauce Connect
When the FileSender installation isn't accessible on a public domain, you are still able to run it on Sauce Labs using a tunnel. To do this download the Sauce Connect Binary, found at:
https://wiki.saucelabs.com/display/DOCS/Setting+Up+Sauce+Connect
Unzip the file, then go to the location where the file has been exstracted using a terminal window. Then enter the following command:

	`bin/sc --user <User Name Sauce Labs> --api-key <Api key Sauce Labs>`
	
For Windows enter the following command instead:

	`bin/sc.exe --user <User Name Sauce Labs> --api-key <Api key Sauce Labs>`

###Setting up API key
Go to the API project, and open the config.php file.
Add / replace the following values:

`$config['sauce_username']    =  <User Name Sauce Labs>`
`$config['sauce_access_key']  =  <Api key Sauce Labs>`

### Running tests
Open a terminal window in the project

To run the selenium tests, enter the following command:

	`vendor/phpunit/phpunit/phpunit --testsuite="Selenium Test Suite"`
	
The tests will appear one by one on the Sauce Labs dashboard. When a tests fails, it will be colored red. Click on the test to find out more on why it failed

### Setting up Firefox plugin
To simplify the writing of tests, we have put an 'selenium-ide.xpi' file in the Docker Repository. This can be found in the /plugins/ folder

This is a Firefox plug-in and can be installed by dragging it into a new Firefox instance. If Firefox rejects the plug-in, it could be because of a setting in the configuration of Firefox. This can be solved by going to about:config and setting xpinstall.signatures.required to false

### Using the Firefox plug-in
Start the Firefox plugin ( by pressing the button in the Plug-in bar in the top right corner). When the  plug-in is booted, a window will appear. Press the record button in the top right cornor of this new window. After that go to a tab in Firefox where the current FileSender installation is running and perform the steps you want to test. While you are doing this, the same steps will appear in the plugin window. When you are finished performing the steps, go back to the plug-in window and click on File 'Export Test Case As' Php / Codebridge Tests Anonymous. De plug-in will ask where to put the new test. You can save it directly in the selenium_test/anonymous_tests folder in the FileSender API project. This test will be run when the Test Suite is activated.

In the previous segment we discussed setting up an 'Anonymous Test'. This means that the user wasn't logged in when the test started. To start a test form the dashboard of a test user, we can create a 'User Test'. This can be created by first logging in in the Firefox window before creating the tests. The test can then be exported by going to File 'Export Test Case As' Php / Codebridge Tests User. The exported file can then be saved to the selenium_tests/user_tests folder. To configure under which user the tests should be running, you can modify the credentials in the doLogin function in selenium_tests/SeleniumUserTest.php

Unfortunatly the test that are written by the Firefox Plug-in don't always work. The tests that are written by this plugin don't take into account the loading times between the actions the tests perform. To account for this, time-outs need to be set-up. Triggers can also be set so the test will wait for a certain element to become available. This as already been done in some of the current tests. There is also documentation available on-line on how to write Selenium Tests in Php.