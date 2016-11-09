<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class EncryptionTest extends SeleniumTest {

    /**
     * Method testEncryptionTest 
     * @test 
     */
    public function testEncryptionTest() {
        extract($this->getKeyBindings());
        
        $checkbox = $this->byCssSelector('[name="get_a_link"]');
        if (!$checkbox->selected()) {
            $checkbox->click();
        }
        
        // Turn on encrption
        $this->byId("encryption")->click();
        
        // Set encryption password
        $this->byName("encryption_password")->clear();
        $this->byName("encryption_password")->value("123123");
        
        // Upload files
        $this->uploadFiles();
        
        // check for progress
        //$this->assertContains('%', $this->byCssSelector("#page .box")->text());
        
        sleep(10);
        
        $statement = DBI::prepare('SELECT * FROM files ORDER BY id DESC LIMIT 1');
        $statement->execute(['a' => 0]);
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

        $test1_file = "unittests/selenium_tests/assets/124bytes.txt";
        $test1_file_data = file_get_contents($test1_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test1_file);

        return [$test1_file_data];
    }
    
    /**
     * Method testEncryptionTest 
     * @test 
     */
    public function testDecryptionTest() {
        extract($this->getKeyBindings());
        
        // Turn on encrption
        $this->url(Config::get('site_url') . '?s=transfers');
        
        sleep(10);
        
        $this->byCss(".expand")->click();
        sleep(2);
        
        // click download
        $this->byCss(".transfer-download")->click();
        
        // set password
        $this->byCss(".ui-dialog-content.ui-widget-content .wide")->value("1231223");
        
        // click ok
        $this->byCss(".ui-dialog .ui-button.ui-widget.ui-state-default.ui-corner-all.ui-button-text-only")->click();
        sleep(20);
        //
        try{
            $this->acceptAlert();
            
            $this->assertTrue(true);
        }
        catch(PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e){
            $this->assertTrue(false);
        }
        
         // click download
        $this->byCss(".transfer-download")->click();
        
        // set password
        $this->byCss(".ui-dialog-content.ui-widget-content .wide")->value("123123");
        
        // click ok
        $this->byCss(".ui-dialog .ui-button.ui-widget.ui-state-default.ui-corner-all.ui-button-text-only")->click();
        sleep(20);
        //
        try{
            $this->acceptAlert();
            
            $this->assertTrue(false);
        }
        catch(PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e){
            $this->assertTrue(true);
        }
        
        
    }

}
