<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class MaxUploadSizeCest
{
    public function _before(AcceptanceTester $I)
    {
    }


    public function testRemoveFileUpload( AcceptanceTester $I, FileSender $fs )
    {
        $start_upload = false;
        $test_file = '124bytes.txt';
        
        $fs->setupAuth();
        $fs->setMaxTransferFileSize(125);

        $this->fileUploadTest( $I, $fs, '124bytes.txt', false);
        

        $I->click(".remove");

        $this->fileUploadTest( $I, $fs, '124bytes.txt', false);

        // reset
        $fs->setMaxTransferFileSize();
    }


    public function testGoodFileUpload( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setupAuth();
        $fs->setMaxTransferFileSize(125);

        $this->fileUploadTest( $I, $fs, '124bytes.txt', false);

        // reset
        $fs->setMaxTransferFileSize();
    }

    public function testFileTooBigUpload( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setupAuth();
        $fs->setMaxTransferFileSize(124);

        $this->fileUploadTest( $I, $fs, '125bytes.txt', true);

        // reset
        $fs->setMaxTransferFileSize();
    }


    private function fileUploadTest( AcceptanceTester $I, FileSender $fs,
                                     $file_name, $error_expected )
    {
        $start_upload = false;
        $fs->upload( array('get_a_link' => true),
                     $file_name, $start_upload );

        if( $error_expected ) {
            $I->see("Maximum transfer size exceeded");
        } else {
            $I->dontSee("Maximum transfer size exceeded");
        }
    }



    
}
