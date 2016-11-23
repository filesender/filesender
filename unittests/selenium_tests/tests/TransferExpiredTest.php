<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class TransferExpiredTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testTransferExpired()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();
        
        $checkbox = $this->byCssSelector('[name="get_a_link"]');
        if (!$checkbox->selected()) {
            $checkbox->click();
        }
        
        ${"temp"} = $this->execute(array('script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args' => array()));

        $this->setMaxTransferFileSize(1024);
        
        $test1_file = "unittests/selenium_tests/assets/124bytes.txt";
        $test1_file_data = file_get_contents($test1_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test1_file);

        $test2_file = "unittests/selenium_tests/assets/125bytes.txt";
        $test2_file_data = file_get_contents($test2_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test2_file);

        $this->byCssSelector('.start.ui-button')->click();


        $this->waitUntil(function(){
            $elements = $this->elements($this->using('css selector')->value('.ui-dialog-content.ui-widget-content.success'));
            $count = count($elements);
            if($count > 0)
            {
                return true;
            }
        }, 10000);

       
        $this->waitUntil(function(){

            $elements = $this->elements($this->using('css selector')->value('.ui-dialog-content.ui-widget-content.success textarea'));

            if(count($elements) > 0){
                $container = array_pop($elements);
                $url = $container->value();

                return true;
            }   
        }, 10000);

        $this->checkDownloadUrl($url, [$test1_file_data, $test2_file_data]);
    }
}
