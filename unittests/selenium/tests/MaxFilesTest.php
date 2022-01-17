<?php

require_once 'unittests/selenium/SeleniumTest.php';

// requires a config
// Config::set( 'max_transfer_files', 30 );


class MaxFilesTest extends SeleniumTest
{

    protected $start_url_path = '';
    const MAXFILECOUNT = 30;
    

    public function testEnoughFiles()
    {
        $this->fileQuantityTest(self::MAXFILECOUNT, false);

    }


    public function testTooMuchFiles()
    {
        $this->fileQuantityTest(self::MAXFILECOUNT+1, true);
    }

    private function fileQuantityTest($number_of_files, $error_expected)
    {
        $test = $this;
        extract($this->getKeyBindings());
        $this->setMaxTransferFileCount(self::MAXFILECOUNT);

        $this->setupAuthenticated();
        $this->showFileUploader();
        
        $testfiles = array();
        for($i = 0; $i < $number_of_files; $i++)
        {
            $fp = $this->addFile( "testseries".$i.".txt" );
            $testfiles[] = $fp;
        }
        sleep(2);

        if( $error_expected ) {
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file.invalid.transfer_too_many_files', 1 ),
                                 "too many files message must be shown for a file" );
        } else {
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file',$number_of_files),
                                 "all the files are shown in a list" );
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file.invalid.transfer_too_many_files', 0 ),
                                 "no files have shown as being too many files" );
        }
    }


    public function tearDown()
    {
        $this->setMaxTransferFileCount();
        $this->setMaxTransferFileSize();
    }

}
