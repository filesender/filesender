<?php

declare(strict_types=1);

namespace Tests\Support\Page\Acceptance;

use Codeception\Util\Locator;


class FileSender
{
    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public $usernameField = '#username';
     * public $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * @var \Tests\Support\AcceptanceTester;
     */
    protected $acceptanceTester;

    private $downloadDir = "/home/testdriver/Downloads/";

    public function __construct(\Tests\Support\AcceptanceTester $I)
    {
        $this->acceptanceTester = $I;
        $this->AuthSetup = false;
        // you can inject other page objects here as well
        $this->clearCustomConfig();
    }

    public function setupAuth()
    {
        $I = $this->acceptanceTester;

        if( $this->AuthSetup ) {
            return;
        }
        
        $I->amOnPage('/');
        $I->see('Share your files safely');
        
        $I->click('#btn_logon');
        $I->see('Enter your username and password');
        $I->fillField('username', 'testdriver@localhost.localdomain');
        $I->fillField('password', 'hello');
        $I->click('#submit_button');
        $I->wait(1);
        $I->see('Get a link instead of sending to recipients');

        $this->AuthSetup = true;
    }


    private function waitForDownloadsChrome()
    {
        $I = $this->acceptanceTester;
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

    public function waitForDownloadToComplete()
    {
        $I = $this->acceptanceTester;
        $fs = $this;
        
        $I->executeJS("window.open('');");
        $I->switchToNextTab();
        $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
            $webdriver->get('chrome://downloads/');
        });

        $v = $fs->waitForDownloadsChrome();
        return $v;
    }
    
    public function gotoLatestTransferDownloadPage()
    {
        $I = $this->acceptanceTester;

        $I->amOnPage('/?s=transfers');
        $I->see('Currently available transfers');
        $I->click(".expand");
        $I->click(".download_href");
    }

    
    public function upload( $options, $filename = null, $startUpload = true, $otherFiles = null )
    {
        $I = $this->acceptanceTester;
        $fs = $this;
        
        $fs->setupAuth();

        ///////////////////////
        // encryption desired?
        //
        if( array_key_exists('encryption_password', $options )) {           

            $I->checkOption("encryption");
            $I->wait(1);
            $I->fillField('encryption_password', $options['encryption_password']);            
        }

        if( !$filename ) {
            $filename = 'test.txt';
        }
        
        $I->executeJS("var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';");

        
        $I->attachFile('.file_selector input[name="files"]', $filename );
        if( $otherFiles ) {
            foreach( $otherFiles as $f ) {
                $I->attachFile('.file_selector input[name="files"]', $f );
            }
        }
        
        if( $startUpload ) {
            $I->click('.start.ui-button');
        
            $I->see('Major upload');
            $I->waitForText('Done uploading', 30); // secs
        
            // $I->wait(2);
        }
        
    }
/*
    public function waitFor( $func, $limit = 30 ) {
        $I = $this->acceptanceTester;
        $fs = $this;

        $I->wait(1);
        for( $i = 0; $i < $limit; $i++ ) {
            $v = $func( $I, $fs );
            if( $v ) {
                return $v;
            }
            $I->wait(1);
        }
        return null;
    }
 */

    private $customConfig = array();
    
    public function wipeCustomConfig()
    {
        $this->clearCustomConfig();
        $this->writeCustomConfig();
    }

    public function clearCustomConfig()
    {
        $this->customConfig = array();
        $this->customConfig['testsuite_run_locally'] = true;
    }
    public function setConfig( $k, $v )
    {
        $this->customConfig[$k] = $v;
    }
        
    public function setKeyVersionNewFiles($v = 0)
    {
        $I = $this->acceptanceTester;
        $fs = $this;
        
        $this->encryption_key_version_new_files = $v;
        $this->customConfig['encryption_key_version_new_files'] = $v;
        $this->writeCustomConfig();
    }

    public function writeCustomConfig()
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $f = fopen('/opt/filesender/config/config_custom.php','w');
        fwrite( $f, "<?php\n" );
        fwrite( $f, "\n" );

        foreach ($this->customConfig as $k => $v) {
            if($k == "PUT_PERFORM_TESTSUITE" ) {
                fwrite( $f, '$config["' . $k . '"] = \''. $v . '\';' . "\n" );
            } else {
                fwrite( $f, '$config["' . $k . '"] = "'. $v . '";' . "\n" );
            }
        }
        fclose($f);

        sleep(1);
        $I->reloadPage();
        sleep(1);        
    }

    public function ensureLogoff()
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        if( $I->tryToSee('Log-off')) {
            $I->click('#topmenu_logoff');
            sleep(1);        
            $I->amOnPage('/');
            sleep(1);        
        }
        
    }

    public function ensureLogon()
    {
        $this->setupAuth();
/*        
        if( $this->authType == 'fake' ) {
            return;
        }

        $fs->clearCustomConfig();
        $fs->setConfig('auth_sp_type', "'fake'");
        $this->authType = 'saml';
        $fs->writeCustomConfig();
 */
        
    }

    
    public function elementIsPresent($I, $element)
    {
        try {
            $I->seeElement($element);
            $isFound = true;
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $isFound = false;
        }
        return $isFound;
    }

    public function setUserPage()
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $k = 'user_page';
        $v = 'array(\'lang\'=>true,\'auth_secret\'=>true,\'id\'=>true,\'created\'=>true)';
        $this->customConfig[$k] = $v;
        $fs->setConfig($k, $v );
        $fs->writeCustomConfig();        
    }

    public function setAdmin($v)
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $k = 'admin';
        $this->customConfig[$k] = $v;
        $fs->setConfig($k, $v );
        $fs->writeCustomConfig();        
    }

    public function setInvalidExtensions($invalid_extensions = "exe,bat")
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $k = 'ban_extension';
        $v = $invalid_extensions;
        $this->customConfig[$k] = $v;
        $fs->setConfig($k, $v );
        $fs->writeCustomConfig();        
    }


    public function setMaxTransferFileSize($max_file_size = 2107374182400)
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $k = 'max_transfer_size';
        $v = $max_file_size;
        $this->customConfig[$k] = $v;
        $fs->setConfig($k, $v );
        $fs->writeCustomConfig();        
    }
    
    public function function_override_clear_all()
    {
        $this->clearCustomConfig();
//        $this->customConfig["PUT_PERFORM_TESTSUITE"] = "''";
        unset($this->customConfig["PUT_PERFORM_TESTSUITE"]);        
        $this->writeCustomConfig();
    }

    public function function_override_set($k, $v)
    {
        $this->clearCustomConfig();
        $this->customConfig["PUT_PERFORM_TESTSUITE"] = $v;
        $this->writeCustomConfig();
    }


    public function uploadEncrypted( $otherFiles = null )
    {
        $I = $this->acceptanceTester;
        $fs = $this;
        
        $fs->upload(
            array('get_a_link' => true,
                  'encryption_password' => '123123',
            ),
            "file20mb.txt", true, $otherFiles );
            
    }

    
    public function downloadEncrypted( $fn = "file20mb.txt" )
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        
        $fs->setupAuth();
        
        $fs->gotoLatestTransferDownloadPage();

        
        $I->click("[data-name='" . $fn . "'] .download");
        $I->wait(1);
        $I->fillField('.ui-dialog-content.ui-widget-content .wide', '123123x');
        $I->click(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button");
        $I->wait(1);
        $I->retrySeeInPopup("Incorrect");
        $I->wait(1);
        $I->retryAcceptPopup();
        $I->wait(1);
        $I->retrySee('Incorrect Password');


        $I->click("[data-name='" . $fn . "'] .download");
        $I->wait(1);
        $I->fillField('.ui-dialog-content.ui-widget-content .wide', '123123');
        $I->click(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button");

        // run the auto streamsaver dialog handler.
        exec(dirname(__FILE__) . "/../../../" . "/accept-dialog.sh");
        
        $I->wait(3);
        $I->retrySee('Download complete');
//        $I->wait(30);
        
    } 


    public function uploadNotEncrypted( $otherFiles = null )
    {        
        $I = $this->acceptanceTester;
        $fs = $this;

        $fs->upload(
            array('get_a_link' => true,
            ),
            "file20mb.txt", true, $otherFiles );
    }
    
    public function downloadNotEncrypted( $fn = "file20mb.txt" )
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $fs->setupAuth();

        $fs->gotoLatestTransferDownloadPage();
        $I->click("[data-name='" . $fn . "'] .download");

        $v = $dlfn = $fs->waitForDownloadToComplete();
        
        $I->assertNotSame("",print_r($v,true),"no contents downloaded");

//        $fn = $I->grabTextFrom('.file .name');
        $I->assertNotSame("",$fn);
        $fnhash = sha1(file_get_contents("./unittests/codeception/Support/Data/" . $fn));
        $I->assertNotSame("",$fnhash);

        $dlhash = sha1(file_get_contents($dlfn));
        $I->assertNotSame("",$dlhash);

        $I->assertSame($fnhash,$dlhash);
    }    


    public function verifyFile( $fn, $downloadPath )
    {
        $I = $this->acceptanceTester;
        $fs = $this;

        $I->assertNotSame("",$fn);
        $I->assertNotSame("",$downloadPath);
        $I->assertNotSame($fn,$downloadPath);
        
        $fnhash = sha1(file_get_contents("./unittests/codeception/Support/Data/" . $fn));
        $I->assertNotSame("",$fnhash);

        $dlhash = sha1(file_get_contents($downloadPath));
        $I->assertNotSame("",$dlhash);

        $I->assertSame($fnhash,$dlhash);
    }
    
    
    public function downloadEncryptedArchive( $expectedItems )
    {
        $I = $this->acceptanceTester;
        $fs = $this;


        
        $fs->setupAuth();
        
        $fs->gotoLatestTransferDownloadPage();
        

        $I->click(".archive_download");
        $I->wait(1);
        $I->fillField('.ui-dialog-content.ui-widget-content .wide', '123123');
        $I->click(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button");

        // run the auto streamsaver dialog handler.
        exec(dirname(__FILE__) . "/../../../" . "/accept-dialog.sh");
        
        $I->wait(5);
        $I->retrySee("Download complete", ".archive_message");
        

        //
        // Check the sha1 for all the expected files in the downloaded zip
        //
        exec(dirname(__FILE__) . "/../../../" . "/fix-download-permissions.sh");
        
        $tmpdir = "/tmp/selenium-downloads/";
        $fn = trim(file_get_contents($tmpdir . "/active.txt"));
        $I->assertNotSame("",$fn);
        
        foreach( $expectedItems as $delfn ) {
            $p = $tmpdir.$delfn;
            if( file_exists($p)) {
                $I->assertNotSame("deleting file $delfn",$p);                
                unlink($p);
            }
        }
        
        copy($this->downloadDir . $fn . ".zip", $tmpdir . "/$fn.zip" );
        $oldpwd = getcwd();
        chdir($tmpdir);
        exec("unzip -o  $fn.zip");
        chdir($oldpwd);

        foreach( $expectedItems as $fn ) {
            $this->verifyFile( $fn, $tmpdir . $fn );
        }
        

        
    } 
    
}
