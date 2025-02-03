<?php

namespace Tests\Acceptance;

include_once('vendor/autoload.php');
include_once('classes/utils/TestSuiteSupport.class.php');


use Tests\Support\AcceptanceTester;
//use \Codeception\FileSenderTrait;
use \Tests\Support\Page\Acceptance\FileSender;

class EncryptionCest 
{
    
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    private function uploadEncrypted( AcceptanceTester $I, FileSender $fs )
    {        
        $fs->upload(
            array('get_a_link' => true,
                  'encryption_password' => '123123',
            ));
    }

    
    private function downloadEncrypted( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setupAuth();
        
        $fs->gotoLatestTransferDownloadPage();
        $I->click(".download");
        $I->wait(1);
        $I->fillField('.ui-dialog-content.ui-widget-content .wide', '123123x');
        $I->click(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button");

        
        $I->wait(1);
        $I->retrySeeInPopup("Incorrect");
        $I->wait(1);
        $I->retryAcceptPopup();
        $I->wait(1);
        $I->retrySee('Incorrect Password');


        $I->click(".download");
        $I->wait(1);
        $I->fillField('.ui-dialog-content.ui-widget-content .wide', '123123');
        $I->click(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button");

        $I->wait(1);
        $I->retrySee('Download complete');
//        $I->wait(30);
        
    } 
    
///////////////////////////////////////////
// test with no encryption just for laughs
///////////////////////////////////////////
    
    
    public function uploadNotEncrypted( AcceptanceTester $I, FileSender $fs )
    {        
        $fs->upload(
            array('get_a_link' => true,
            ));
    }
    
    public function downloadNotEncrypted( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setupAuth();

        $fs->gotoLatestTransferDownloadPage();
        $I->click(".download");

        $v = $dlfn = $fs->waitForDownloadToComplete();
        
        $I->assertNotSame("",print_r($v,true));

        $fn = $I->grabTextFrom('.file .name');
        $I->assertNotSame("",$fn);
        $fnhash = sha1(file_get_contents("./unittests/codeception/Support/Data/" . $fn));
        $I->assertNotSame("",$fnhash);

        $dlhash = sha1(file_get_contents($dlfn));
        $I->assertNotSame("",$dlhash);

        $I->assertSame($fnhash,$dlhash);
    }    

/////////////////////////
// Main tests
/////////////////////////


    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 0
     * @test 
     */
    public function testEncryptionKeyVerZeroTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 0 );
        $this->uploadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerZeroTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 0 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerZeroOneTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted( $I, $fs );
    }

    
    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 1
     * @test 
     */
    public function testEncryptionKeyVerOneTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 1 );
        $this->uploadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerOneTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 0 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerOneOneTest( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted( $I, $fs );
    }



    /**
     * Method testEncryptionTest 
     * upload a file using key_version = 3
     * @test 
     */
    public function testEncryptionKeyVerThreeTest( AcceptanceTester $I, FileSender $fs ) {
        $fs->setKeyVersionNewFiles( 3 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerThreeTest( AcceptanceTester $I, FileSender $fs ) {
        $fs->setKeyVersionNewFiles( 0 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerThreeOneTest( AcceptanceTester $I, FileSender $fs ) {
        $fs->setKeyVersionNewFiles( 1 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerThreeTwoTest( AcceptanceTester $I, FileSender $fs ) {
        $fs->setKeyVersionNewFiles( 2 );        
        $this->downloadEncrypted( $I, $fs );
    }
    public function testDecryptionKeyVerThreeThreeTest( AcceptanceTester $I, FileSender $fs ) {
        $fs->setKeyVersionNewFiles( 3 );        
        $this->downloadEncrypted( $I, $fs );
    }


    
}
