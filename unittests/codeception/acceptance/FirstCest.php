<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

include_once('vendor/autoload.php');
include_once('classes/utils/TestSuiteSupport.class.php');



class FirstCest
{
    private function setupAuth(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Share your files safely');
        
        $I->click('#btn_logon');
        $I->see('Enter your username and password');
        $I->fillField('username', 'testdriver@localhost.localdomain');
        $I->fillField('password', 'hello');
        $I->click('#submit_button');
        $I->see('Get a link instead of sending to recipients');
    }
    
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    public function testsomething( AcceptanceTester $I, FileSender $fs )
    {        
        $fs->function_override_clear_all();
    }

/*        
    public function frontpageWorks(AcceptanceTester $I)
    {
        $this->setupAuth( $I );

        
        $I->executeJS("var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';");
        
//        $I->wait(2);
        $I->attachFile('.file_selector input[name="files"]', 'test.txt' );
//        $I->wait(2);
        $I->click('.start.ui-button');

        
//        $I->wait(2);
        
        $I->see('Major upload');
        $I->waitForText('Done uploading', 30); // secs
//        $I->wait(10);
    }
*/

    private function waitForDownloads(AcceptanceTester $I)
    {
        $limit = 30;

        for( $i = 0; $i < $limit; $i++ ) {
            
            $v =  $I->executeJS(<<<EOT

            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList').items;
console.log(v);


            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList')
	    .items.filter(e => e.state != '2')
.length;console.log('AA');
console.log(v);
            if( v > 0 ) {
                return false;
            }
            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList').items;
console.log('BB');
console.log(v);
return v[0].filePath;
EOT );


            if( $v != '' ) {
	        $I->closeTab();
                return $v;
            }
            $I->wait(1);
        }
	$I->closeTab();
    }

    public function downloadraw(AcceptanceTester $I)
    {
/*        
        $this->setupAuth( $I );

        $I->amOnPage('/?s=transfers');
        $I->see('Currently available transfers');
        $I->click(".expand");
        $I->click(".download_href");
        $I->click(".download");

        $I->executeJS("window.open('');");
        $I->switchToNextTab();
        $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
            $webdriver->get('chrome://downloads/');
        });

        $v = $this->waitForDownloads($I);
        $dlfn = $v;
        
        $I->assertNotSame("",print_r($v,true));
//        $I->assertEquals("x",print_r($v,true));
        //        $I->wait(90);

        // 'test.txt'
        // ./tests/Support/Data/test.txt

        $fn = $I->grabTextFrom('.file .name');
        $I->assertNotSame("",$fn);
        $fnhash = sha1(file_get_contents("./tests/Support/Data/" . $fn));
        $I->assertNotSame("",$fnhash);

        $dlhash = sha1(file_get_contents($dlfn));
        $I->assertNotSame("",$dlhash);

        $I->assertSame($fnhash,$dlhash);
  */      
            
//$originalhash = system("md5sum ~/testdata/$filename | cut -d' ' -f1 ");

        
/*
        $I->click('#btn_logon');
        $I->see('Enter your username and password');
        $I->fillField('username', 'testdriver@localhost.localdomain');
        $I->fillField('password', 'hello');
        $I->click('#submit_button');
        $I->see('Get a link instead of sending to recipients');

        $I->executeJS("var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';");
        
//        $I->wait(2);
        $I->attachFile('.file_selector input[name="files"]', 'test.txt' );
//        $I->wait(2);
        $I->click('.start.ui-button');

        
//        $I->wait(2);
        
        $I->see('Major upload');
        $I->waitForText('Done uploading', 30); // secs
        $I->wait(10);
 */
        
    }    
    
}
