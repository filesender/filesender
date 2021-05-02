<?php

include_once('unittests/selenium/SeleniumTest.php');

class UploadAutoResumeTest extends SeleniumTest
{

    protected $start_url_path = '';

    public static function cb_testGoodFileUpload($data, $file, $id = null, $mode = null, $offset = null) {
        if( $file->name == "file10mb.txt" ) {
            if( TestSuiteSupport::serverside_guard_first_call( "put_perform_testsuite_file10mb.txt" )) {
                throw new RestCannotAddDataToCompleteTransferException("File", $file->id);
            }    
        }
    }

    public function testclearServerSideTestLog()
    {
        $this->url(Config::get('site_url') . '/rest.php/utility/cleartestlog');
        sleep(10);
        $this->assertEquals(1,1);
    }

    public function testGoodFileUpload()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();
        $this->setMaxTransferFileSize();

        TestSuiteSupport::function_override_clear_all();
        TestSuiteSupport::function_override_set(
           'PUT_PERFORM_TESTSUITE','UploadAutoResumeTest::cb_testGoodFileUpload($data,$file,$id,$mode,$offset);');

        $this->showFileUploader();
        sleep(1);
        
        $this->fileUploadTest('file10mb.txt', false);
        sleep(1);
        
        $this->byCssSelector('.start.ui-button')->click();
        sleep(1);
        
        // wait for the dialog
        $url = $this->waitForUploadCompleteDialog();

        TestSuiteSupport::function_override_clear_all();
        
        // echo "url $url \n";
        $this->assertGreaterThan( 20, strlen($url), "bad upload url" );


       $this->setupAuthenticated();
        sleep(5);
	
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
        if (!$this->isCheckBoxSelected('[name="get_a_link"]')) {
            $this->clickCheckbox('[name="get_a_link"]');
        }
        
        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));
    }

    private function fileUploadTest($file_name, $error_expected)
    {
        echo "file_name $file_name \n";
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), "unittests/selenium/assets/".$file_name);

        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid transfer_maximum_size_exceeded"]'));
        $count = count($elements);


        $elements = $this->elements($this->using('css selector')->value('.start.ui-button.ui-button-disabled.ui-state-disabled'));
        $count_button = count($elements);

        $this->assertEquals( ($error_expected?2:0), $count+$count_button);
    }



}
