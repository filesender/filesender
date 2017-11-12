<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class TransferExpiredTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testTransferExpired()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        $this->setMaxTransferFileSize(1024);

        if (!$this->isCheckBoxSelected('[name="get_a_link"]')) {
            $this->clickCheckbox('[name="get_a_link"]');
        }
        
        ${"temp"} = $this->execute(array('script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args' => array()));
        
        $test1_file = "unittests/selenium_tests/assets/124bytes.txt";
        $test1_file_data = file_get_contents($test1_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test1_file);

        $test2_file = "unittests/selenium_tests/assets/125bytes.txt";
        $test2_file_data = file_get_contents($test2_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test2_file);

        $this->byCssSelector('.start.ui-button')->click();

        $test = $this;
        $this->waitUntil(function() use ($test){
            $elements = $test->elements($test->using('css selector')->value('.ui-dialog-content.ui-widget-content.success'));
            $count = count($elements);
            if($count > 0)
            {
                return true;
            }
        }, 10000);


        $url = trim($this->byCssSelector('.ui-dialog-content.ui-widget-content.success textarea')->value());

// need zip64 friendly version.
//        $this->checkDownloadUrl($url, array($test1_file_data, $test2_file_data));
    }
}
