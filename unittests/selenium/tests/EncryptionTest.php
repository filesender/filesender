<?php

require_once 'unittests/selenium/SeleniumTest.php';

class EncryptionTest extends SeleniumTest {

    private function uploadEncrypted() {
        extract($this->getKeyBindings());
        $test = $this;

        $this->setupAuthenticated();


        if (!$this->isCheckBoxSelected('[name="get_a_link"]')) {
            $this->clickCheckbox('[name="get_a_link"]');
        }

        // Turn on encrption
        $this->byId("encryption")->click();

        
        sleep(3);
        // Set encryption password
        $this->byName("encryption_password")->clear();
        $this->byName("encryption_password")->value("123123");
        
        // Upload files
        $this->uploadFiles();

            
        sleep(1);
        $this->byCssSelector('.start.ui-button')->click();

        // wait for the dialog
        $this->waitUntil(function() use ($test){
            $elements = $test->elements($test->using('css selector')->value('.ui-dialog-title'));
            $count = count($elements);
            if($count > 0)
            {
                return true;
            }
        }, 30000);
        // the popup is not instant.. sleep a bit
        sleep(1);
        
        // check for success
        $this->assertContains('Success', $this->byCssSelector('.ui-dialog-title')->text());
        
        // check db for encryption
        $filesTable = call_user_func('File::getDBTable');
        $statement = DBI::prepare('SELECT * FROM ' . $filesTable . ' ORDER BY id DESC LIMIT 1');
        $statement->execute(array());
        $data = $statement->fetch();
        
        $encrypted_succes = false;
        if($data['encrypted_size'] > $data['size']){
            $encrypted_succes = true;
        }
        $this->assertTrue($encrypted_succes);
    }

    private function uploadFiles()
    {
        ${"temp"} = $this->execute(array('script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args' => array()));

        $test1_file = "unittests/selenium/assets/124bytes.txt";
        $test1_file_data = file_get_contents($test1_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test1_file);

        return array($test1_file_data);
    }

    private function waitForCssElement($selector) {
        $test = $this;
        
        $rv = $this->waitUntil(function() use ($test,$selector){
            $elements = $test->elements($test->using('css selector')->value($selector));
            if( count($elements)) {
                return true;
            }
        }, 30000);
        return $rv;
    }

    private function waitForAndEnsureCssElementContains($selector,$needle) {
        $test = $this;
        

//        $v = $this->byCss($selector)->text();
//        $this->assertTrue( false,'waitForAndEnsureCssElementContains(invalid response) v ' . $v . ' selector ' . $selector );

        
        
        $rv = $this->waitUntil(function() use ($test,$selector,$needle){
            try {
                $v = $this->byCss($selector)->text();
                if (strpos($v, $needle) !== false) {
                    return true;
                }
            }
            catch(PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e){
            }
        },5000);
        if( $rv ) {
            $this->assertTrue( $rv,'waitForAndEnsureCssElementContains(invalid response) selector ' . $selector );
        }
        return $rv;
    }
    
    protected function waitForAlert() {
        $test = $this;
        sleep(1);
        $this->waitUntil(function () use ($test) { return $test->alertIsPresent(); }, 30000 );
    }
    
    /**
     */
    private function downloadEncrypted() {
        extract($this->getKeyBindings());
        $test = $this;

        $this->setupAuthenticated();
        
        // Turn on encrption
        $this->url(Config::get('site_url') . '?s=transfers');

        $this->waitForCssElement('.expand');        
        $this->byCss(".expand")->click();

        $this->waitForCssElement('.transfer-download');
        sleep(1);

        $this->byCss(".download_href")->click();
        sleep(2);

        
        // click download
        $this->byCss(".download")->click();
        sleep(5);
        
        // set password
        $this->byCss(".ui-dialog-content.ui-widget-content .wide")->value("1231223");
        
        // click ok
        $this->byCss(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button")->click();
        //        sleep(20);
        $this->waitForAlert();
        //
        try{
            $this->assertContains("Incorrect", $this->alertText());
            
            $this->acceptAlert();
            
            $this->assertTrue(true);
        }
        catch(PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e){
            $this->assertTrue(false);
        }


        
        sleep(5);
         // click download
        $this->byCss(".download")->click();
        sleep(5);
        
        // set password
        $this->byCss(".ui-dialog-content.ui-widget-content .wide")->value("123123");
        
        // click ok
        $this->byCss(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button")->click();
        $this->waitForAndEnsureCssElementContains(".downloadprogress", 'Download complete');
        
        
    }
    
    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 0
     * @test 
     */
    
    public function testEncryptionKeyVerZeroTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 0 );        
        $this->uploadEncrypted();
    }

    /**
     * Method testDecryptionTest 
     * @test 
     */
    public function testDecryptionKeyVerZeroTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 0 );        
        $this->downloadEncrypted();
    }
    
    public function testDecryptionKeyVerZeroOneTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted();
    }


    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 1
     * @test 
     */
    public function testEncryptionKeyVerOneTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 1 );        
        $this->uploadEncrypted();
    }
    public function testDecryptionKeyVerOneTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 0 );        
        $this->downloadEncrypted();
    }
    public function testDecryptionKeyVerOneOneTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted();
    }

    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 3
     * @test 
     */
    public function testEncryptionKeyVerThreeTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 3 );        
        $this->uploadEncrypted();
    }
    public function testDecryptionKeyVerThreeTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 0 );
        // force a page refresh on my transfers.
        $this->url(Config::get('site_url') . '?s=upload');
        sleep(5);
        $this->downloadEncrypted();
    }
    public function testDecryptionKeyVerThreeOneTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted();
    }
    public function testDecryptionKeyVerThreeTwoTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 2 );        
        $this->downloadEncrypted();
    }
    public function testDecryptionKeyVerThreeThreeTest() {
        extract($this->getKeyBindings());
        $this->setKeyVersionNewFiles( 3 );        
        $this->downloadEncrypted();
    }
}
