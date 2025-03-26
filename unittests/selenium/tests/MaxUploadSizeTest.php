<?php

require_once 'unittests/selenium/SeleniumTest.php';

class MaxUploadSizeTest extends SeleniumTest
{
    

    public function testRemoveFileUpload()
    {
        extract($this->getKeyBindings());
        $this->setMaxTransferFileSize(125);
        $this->setupAuthenticated();


        $this->showFileUploader();

        // upload
        $this->fileUploadTest('124bytes.txt', false);

        // remove
        $this->removeUploadStage1('124bytes.txt');

        // upload again
        $this->fileUploadTest('124bytes.txt', false);
    }


    public function testGoodFileUpload()
    {
        extract($this->getKeyBindings());
        $this->setMaxTransferFileSize(125);
        $this->setupAuthenticated();


        $this->showFileUploader();
        $this->fileUploadTest('124bytes.txt', false);

        $this->setMaxTransferFileSize(124);
    }

    public function testFileTooBigUpload()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();

        $this->showFileUploader();
        $this->fileUploadTest('125bytes.txt', true);

        $this->setMaxTransferFileSize();
    }


    private function fileUploadTest($file_name, $error_expected)
    {
        $number_of_files = 1;
        $this->addFile( $file_name );

        // assert that we pass the tests
        $this->waitForFilesListWithPossibleError('.transfer_maximum_size_exceeded',$number_of_files,$error_expected);
        $this->assertStage1ContinueDisabled( $error_expected );
    }


}
