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

        // Set encryption password
        $this->waitForCSS(".encryption_password");
        $this->byName("encryption_password")->clear();
        $this->byName("encryption_password")->value("123123");
        
        // add files to upload
        $filename = "124bytes.txt";
        $this->showFileUploader();
        $originalFilePath = $this->addFile($filename);
        
        $this->stageXContinue(1);
        $this->stageXContinue(2);

        // wait for dialog asserts that we have a link to the download page
        // already
        $url = $this->waitForUploadCompleteDialog();

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


    private function waitForAndEnsureCssElementContains($selector,$needle) {
        $test = $this;
        

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

    
    /**
     */
    private function downloadEncrypted() {
        extract($this->getKeyBindings());
        $test = $this;
        $this->setupAuthenticated();
        
        // Turn on encrption
        $this->url(Config::get('site_url') . '?s=transfers');

        $this->waitForCss('.expand');        
        $this->byCss(".expand > .clickable")->click();

        $this->waitForCss('.transfer-download');
        $this->byCss(".download_href")->click();

        
        // click download
        $this->waitForCss('.download_page');        
        $this->byCss(".download")->click();
        
        // set password
        $this->waitForBootbox();        
        $this->byCss(".bootbox-input-password")->value("1231223");
        $this->byCss(".bootbox-accept")->click();
        // seems you have to give _some_ time here
        sleep(1);
        $this->waitForCss('.bootbox.error-dialog', false );
        $this->waitForCss('.bootbox-body');

        // check that the system noticed the bad password we tried
        try{
            $msg = $this->byCss(".bootbox-body")->text();
            $this->assertContains("Incorrect", $msg);
            
            $this->byCss(".bootbox-accept")->click();
        }
        catch(PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e){
            $this->assertTrue(false);
        }


        // now download it using the correct password
        $this->byCss(".download")->click();
        $this->waitForBootbox();        
        $this->byCss(".bootbox-input-password")->value("123123");
        $this->byCss(".bootbox-accept")->click();
        
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
        $this->waitForStage(1);
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
