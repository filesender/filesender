<?php

require_once 'unittests/selenium/SeleniumTest.php';

class TransferExpiredTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testTransferExpired()
    {
        $test = $this;
        extract($this->getKeyBindings());
        $this->setMaxTransferFileSize(1024);
        $this->setupAuthenticated();

        $fp1 = $this->addFile( "124bytes.txt" );
        $fp1 = $this->addFile( "125bytes.txt" );
        $this->stageXContinue(1);
        $this->scrollToTop();
        $this->ensureTransferGetALink();
        $this->stageXContinue(2);
        $url = $this->waitForUploadCompleteDialog();
//        sleep(1);
        
// need zip64 friendly version.
//        $this->checkDownloadUrl($url, array($test1_file_data, $test2_file_data));
    }
}
