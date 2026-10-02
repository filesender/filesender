<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class MaxFilesCest
{

    private function fileQuantityTest( AcceptanceTester $I, FileSender $fs,
                                       $number_of_files, $error_expected )
    {

        $start_upload = false;
        
        for($i = 0; $i < $number_of_files; $i++)
        {
            $test_file = "file$i.txt";
            $fs->upload( array('get_a_link' => true,),
                         $test_file,
                         $start_upload );
        }

        if( $error_expected ) {
            $I->see("Maximum number of files exceeded");
        } else {
            $I->dontSee("Maximum number of files exceeded");
        }
    }
    

    public function testEnoughFiles( AcceptanceTester $I, FileSender $fs )
    {
        $this->fileQuantityTest( $I, $fs, 30, false);
    }


    public function testTooMuchFiles( AcceptanceTester $I, FileSender $fs )
    {
        $this->fileQuantityTest( $I, $fs, 31, true);
    }

    
}
