<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class UploadAutoResumeTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testGoodFileUpload()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();
        $this->setMaxTransferFileSize();

        $this->showFileUploader();
        sleep(1);

        $this->fileUploadTest('file50mb.txt', false);
        sleep(1);

        $this->byCssSelector('.start.ui-button')->click();


        // wait for the dialog
        $url = $this->waitForUploadCompleteDialog();


        echo "url $url \n";

        $this->assertGreaterThan( 20, strlen($url), "bad upload url" );

    }

    public function waitForUploadCompleteDialog() {
        $test = $this;
        $this->waitUntil(function() use ($test){
            $elements = $test->elements($test->using('css selector')->value('.ui-dialog-title'));
            $count = count($elements);
            if($count > 0)
            {
                return true;
            }
        }, 300 *1000, 500);
        
        $url = trim($this->byCssSelector('.ui-dialog-content.ui-widget-content.success textarea')->value());
        return $url;
    }

    private function showFileUploader()
    {
        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));
    }

    private function fileUploadTest($file_name, $error_expected)
    {
        echo "file_name $file_name \n";
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), "unittests/selenium_tests/assets/".$file_name);

        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid transfer_maximum_size_exceeded"]'));
        $count = count($elements);


        $elements = $this->elements($this->using('css selector')->value('.start.ui-button.ui-button-disabled.ui-state-disabled'));
        $count_button = count($elements);

        $this->assertEquals( ($error_expected?2:0), $count+$count_button);
    }



}
