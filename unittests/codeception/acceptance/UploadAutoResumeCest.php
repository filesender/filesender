<?php


namespace Tests\Acceptance;

include_once(FILESENDER_BASE . "/vendor/autoload.php");
include_once(FILESENDER_BASE . '/classes/utils/TestSuiteSupport.class.php');

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class TestCallbacks
{
    public static function cb_testGoodFileUpload($data, $file, $id = null, $mode = null, $offset = null) {

        $_SESSION['callcount']++;
        
        if ($_SESSION['callcount'] == 1) {
            file_put_contents("/opt/filesender/files/touchy1", "hi there zero 3 " . $_SESSION['count']);
            throw new RestCannotAddDataToCompleteTransferException("File", $file->id);
        }
        file_put_contents("/opt/filesender/files/touchy1", "complete! " . $_SESSION['count']);
    }
}

class UploadAutoResumeCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function testRemoveFileUpload( AcceptanceTester $I, FileSender $fs )
    {
    }



    public function testclearServerSideTestLog( AcceptanceTester $I, FileSender $fs )
    {
        $fs->wipeCustomConfig();
        $fs->setupAuth();
        $I->amOnPage('/rest.php/utility/cleartestlog');
        $I->see('true');
    }
 
    
    public function testGoodFileUpload( AcceptanceTester $I, FileSender $fs )
    {
        $this->I = $I;
        $this->fs = $fs;

        $_SESSION['callcount'] = 0;
        
        $fs->wipeCustomConfig();
        $fs->setupAuth();

        $fs->function_override_clear_all();
        $fs->function_override_set(
            'PUT_PERFORM_TESTSUITE','\Tests\Acceptance\TestCallbacks::cb_testGoodFileUpload($data,$file,$id,$mode,$offset);');

        $file_name = 'file10mb.txt';
        $fs->upload(
            array('get_a_link' => true,),
            $file_name, false );
        $I->click('.start.ui-button');


        $I->comment("hi there");

        $I->see('Major upload');
        $I->waitForText('Done uploading', 30); // secs
        
        $fs->function_override_clear_all();

        // $url = trim($this->byCssSelector('.ui-dialog-content.ui-widget-content.success textarea')->value());
        
        // FIXME add more asserts that things went well here.
        // echo "url $url \n";
//        $this->assertGreaterThan( 20, strlen($url), "bad upload url" );


	
    }


    
}
