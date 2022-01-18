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
        sleep(2);
        $this->assertEquals(1,1);
    }

    public function testGoodFileUpload()
    {
        extract($this->getKeyBindings());

        $filename = 'file10mb.txt';
        
        $this->setMaxTransferFileSize();
        $this->setupAuthenticated();

        TestSuiteSupport::function_override_clear_all();
        TestSuiteSupport::function_override_set(
           'PUT_PERFORM_TESTSUITE','UploadAutoResumeTest::cb_testGoodFileUpload($data,$file,$id,$mode,$offset);');

        
        $this->showFileUploader();
        $originalFilePath = $this->addFile($filename);
        $this->assertUploadPageNoFilesAreTooBig();
        $this->assertUploadPageStage1Continue();
        

        $this->stageXContinue(1);
        $this->stageXContinue(2);
        
        // wait for the dialog
        $url = $this->waitForUploadCompleteDialog();

        TestSuiteSupport::function_override_clear_all();
        
        // echo "url $url \n";
        $this->assertGreaterThan( 20, strlen($url), "bad upload url" );
    }


}
