<?php

require_once 'unittests/selenium/SeleniumTest.php';

class InvalidExtensionsTest extends SeleniumTest
{
    protected $lastTest = false;
    protected $start_url_path = '';
    
    public function testSetup()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        sleep(4);
        $this->assertEquals(1,1,'ok');
    }
    
    public function testBat()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        $this->extensionTest('extensiontest.bat', true);
    }

    public function testExe()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        $this->extensionTest('extensiontest.exe', true);
    }
 

    public function testAllowedExe()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        $this->setInvalidExtensions("'somethingelse,somethingother'");

        $this->extensionTest('extensiontest.exe', false);
        $this->assertEquals(1,1,'ok');
    }
 
    
    public function testInvalidTestExtension()
    {
        $this->lastTest = true;
        
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        $this->setInvalidExtensions("'exe,bat,invalidextension55'");

        $this->extensionTest('extensiontest.invalidextension55', true);
    }

    private function extensionTest($filename, $error_expected)
    {
        $this->showFileUploader();

        $number_of_files = 1;
        $fp = $this->addFile( $filename );

        $this->waitForFilesListWithPossibleError('.banned_extension', $number_of_files, $error_expected );
    }

    
    public function tearDown()
    {
        if( $this->lastTest ) {
            Logger::error("XXX tearDown()" );
            $this->setInvalidExtensions();
        }
    }
}
