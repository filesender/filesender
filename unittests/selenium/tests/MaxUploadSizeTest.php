<?php

require_once 'unittests/selenium/SeleniumTest.php';

class MaxUploadSizeTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testRemoveFileUpload()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        $this->setMaxTransferFileSize(125);

        $this->showFileUploader();

        // upload
        $this->fileUploadTest('124bytes.txt', false);

        // remove
        $this->byCssSelector(".remove")->click();


        // upload again
        $this->fileUploadTest('124bytes.txt', false);

        // reset
        $this->setMaxTransferFileSize();

    }


    public function testGoodFileUpload()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        $this->setMaxTransferFileSize(125);

        $this->showFileUploader();

        $this->fileUploadTest('124bytes.txt', false);

        // reset
        $this->setMaxTransferFileSize();

    }

    public function testFileTooBigUpload()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        $this->setMaxTransferFileSize(124);

        $this->showFileUploader();

        $this->fileUploadTest('125bytes.txt', true);

        // reset
        $this->setMaxTransferFileSize();

    }

    private function showFileUploader()
    {
        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));
    }

    private function fileUploadTest($file_name, $error_expected)
    {
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), "unittests/selenium/assets/".$file_name);

        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid transfer_maximum_size_exceeded"]'));
        $count = count($elements);


        $elements = $this->elements($this->using('css selector')->value('.start.ui-button.ui-button-disabled.ui-state-disabled'));
        $count_button = count($elements);

        $this->assertEquals( ($error_expected?2:0), $count+$count_button);
    }



}
