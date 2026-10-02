<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class InvalidExtensionsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    private function extensionTest( AcceptanceTester $I, FileSender $fs,
                                    $file_extension, $ban_expected )
    {
        $start_upload = false;
        $test_file = 'extensiontest.'.$file_extension;
        
        $fs->upload( array('get_a_link' => true,),
                     $test_file,
                     $start_upload );
        $I->wait(1);

        $I->seeNumberOfElements( '*[class="file invalid banned_extension"]',
                                 $ban_expected ? 1 : 0);
        
        //reset
        $fs->setInvalidExtensions();
    }
    
    public function testBat( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setInvalidExtensions();
        $this->extensionTest($I, $fs, 'bat', true);
    }


    public function testExe( AcceptanceTester $I, FileSender $fs )
    {
        $this->extensionTest($I, $fs, 'exe', true);
    }

    public function testAllowedExe( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setInvalidExtensions("somethingelse,somethingother");
        $this->extensionTest($I, $fs, 'exe', false);
    }

    public function testInvalidTestExtension( AcceptanceTester $I, FileSender $fs )
    {
        $invalid_file_extension = "50";
        $fs->setInvalidExtensions("exe,bat,$invalid_file_extension");
        $this->extensionTest($I, $fs, $invalid_file_extension, true);
    }
 
    
}
