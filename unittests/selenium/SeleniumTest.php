<?php

include_once('vendor/autoload.php');
include_once('classes/utils/TestSuiteSupport.class.php');

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class SeleniumTest extends Sauce\Sausage\WebDriverTestCase
{
    protected $start_url_path = '';

    protected $use_mails = false;

    protected $encryption_key_version_new_files = 0;
    protected $authType = '';

    public static $browsers = array(
        // run FF15 on Windows 8 on Sauce
        //        array(
        //            'browserName' => 'firefox',
        //            'desiredCapabilities' => array(
        //                'version' => '15',
        //                'platform' => 'Windows 2012',
        //            )
        //        ),
        // run Chrome on Linux on Sauce
        array(
            'browserName' => 'chrome',
            'desiredCapabilities' => array(
                'platform' => 'Linux',
                'version' => '84'
            )
        ),
        // run Mobile Safari on iOS
        //array(
        //'browserName' => '',
        //'desiredCapabilities' => array(
        //'app' => 'safari',
        //'device' => 'iPhone Simulator',
        //'version' => '6.1',
        //'platform' => 'Mac 10.8',
        //)
        //)//,
        // run Chrome locally
        //array(
        //'browserName' => 'chrome',
        //'local' => true,
        //'sessionStrategy' => 'shared'
        //)
    );

    public function setUp()
    {
        $caps = $this->getDesiredCapabilities();
        $this->setBrowserUrl('');
        if (!isset($caps['name'])) {
            $caps['name'] = get_called_class().'::'.$this->getName();
            $this->setDesiredCapabilities($caps);
        }

        $tunnelId = getenv('SAUCE_TUNNEL_IDENTIFIER');
        if ($tunnelId) {
            $caps = $this->getDesiredCapabilities();
            $caps['tunnel-identifier'] = $tunnelId;
            $this->setDesiredCapabilities($caps);
        }

        $this->setSeleniumServerRequestsTimeout(120);


        $this->setDesiredCapabilities([
            'goog:chromeOptions' => [
                'w3c' => false,
                'args' => ['--ignore-certificate-errors']
            ]
         ]);

         parent::setUp();
    }

    public static function browsers() {
        require_once('includes/init.php');

        if( Config::get('testsuite_run_locally') == '1' || Config::get('testsuite_run_locally') == 'true' ) {
        echo "running test suite locally\n";
            return array(
                // run Chrome on Linux locally
                array(          
                    'browserName' => 'chrome',
                    'local' => true,        
                    'desiredCapabilities' =>         array(
                        'platform' => 'Linux',
                        'version' => '84'
                    )
                )
            );
        }

        return parent::browsers();
    }
    
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        require_once('includes/init.php');



	
        if(getenv('SAUCE_USERNAME') === false)
        {
		echo "sauce username is not in env";

            if (Config::get('sauce_username') == null || Config::get('sauce_access_key') == null) {
                die('Sauce credentials not set!');
            }
            putenv('SAUCE_USERNAME='.Config::get('sauce_username'));
            putenv('SAUCE_ACCESS_KEY='.Config::get('sauce_access_key'));

        }

        // This block allows us to run against sauce 0.18
        if (!defined('SAUCE_USERNAME')) {
            define('SAUCE_USERNAME', getenv('SAUCE_USERNAME'));
        }
        if (!defined('SAUCE_ACCESS_KEY')) {
            define('SAUCE_ACCESS_KEY', getenv('SAUCE_ACCESS_KEY'));
        }

        if (!defined('SAUCE_VERIFY_CERTS')) {
            if(getenv('SAUCE_DONT_VERIFY_CERTS')) {
                $env_sauce_dont_verify_certify = getenv('SAUCE_DONT_VERIFY_CERTS');
                define('SAUCE_VERIFY_CERTS', empty($env_sauce_dont_verify_certify));
            } else {
                define('SAUCE_VERIFY_CERTS', true);
            }
        }
        $sauce_host = 'saucelabs.com';
        if(getenv('SAUCE_HOST')){
            $sauce_host = getenv('SAUCE_HOST');
        }        
        if(!defined('SAUCE_HOST')) {
            define('SAUCE_HOST', $sauce_host);
        }

        
        $this->start_url = Config::get('site_url');

        if($this->start_url_path != '') {
            $this->start_url = Config::get('site_url') . '/' . $this->start_url_path;
        }

        if($this->use_mails)
        {
            //$this->deleteDirectory('testmails');
        }


        parent::__construct($name, $data, $dataName);
        
    }

    public function setUpPage()
    {
        // set the method which knows if this is a file we're trying to upload
        $this->fileDetector(function($filename) {
            if(file_exists($filename)) {
                return $filename;
            } else {
                echo "File by filename: ". $filename ." not found!\n";
                return NULL;
            }
        });

        return parent::setUpPage();
    }

    protected function getKeyBindings()
    {
        $key_bindings = array();
        
        $refl = new ReflectionClass('PHPUnit_Extensions_Selenium2TestCase_Keys');
        foreach ($refl->getConstants() as $constant_key=>$constant_value)
        {
            $key_bindings[strtolower($constant_key)] = $constant_value;
        }

        return $key_bindings;
    }

    public function tearDown()
    {
        if($this->use_mails)
        {
            $this->deleteDirectory('testmails');
        }

        parent::tearDown();
    }

    protected function setupUnauthenticated()
    {
        $this->changeConfigValue('auth_sp_type', "'saml'");
        $this->waitForPageLoaded( true );
        $this->authType = 'saml';
    }

    /**
     * Set the auth to fake and do a page refresh. If a test wants to
     * adjust settings with changeConfigValue() it should do that before
     * calling this method to avoid sleep()ing and refresh()ing more than
     * is really needed.
     */
    protected function setupAuthenticated()
    {
        if( $this->authType == 'fake' ) {
            return;
        }
        if( Config::get('auth_sp_type') == 'fake' ) {
            Logger::info("setupAuthenticated() already fake auth... " );
            return;
        }
        $this->changeConfigValue('auth_sp_type', "'fake'");
        $this->waitForPageLoaded( true );
        $this->authType = 'fake';
    }

    protected function setAdmin()
    {
        $this->changeConfigValue('admin', "'1'");
        $this->waitForPageLoaded( true );
    }

    protected function unsetAdmin()
    {
        $this->changeConfigValue('admin', "'0'");
        $this->waitForPageLoaded( true );
    }

    protected function setUserPage()
    {
        $this->changeConfigValue('user_page', 'array(\'lang\'=>true,\'auth_secret\'=>true,\'id\'=>true,\'created\'=>true)');
        $this->waitForPageLoaded( true );
    }

    protected function unsetUserPage()
    {
        $this->changeConfigValue('user_page', 'array()');
        $this->waitForPageLoaded( true );
    }

    protected function setMaxTransferFileSize($max_file_size = 2107374182400)
    {
        $this->changeConfigValue('max_transfer_size', $max_file_size);
        $this->waitForPageLoaded( true );
    }
    protected function setMaxTransferFileCount( $c = 30 )
    {
        $this->changeConfigValue('max_transfer_files', $c );
    }

    protected function waitForPageLoaded( $refreshFirst = true )
    {
        $test = $this;
        if( $refreshFirst ) {
            $this->refresh();
        }

        sleep(1);
        
        $rv = $this->waitUntil(function() use ($test) {
            $v = $test->execute(array('script' => "if(!('filesender' in window)) { return false;} return window.filesender.pageLoaded;",
                                      'args' => array()));
            Logger::Error( "have v $v ");
            if( $v > 0 ) {
                return true;
            }
            return null;
        },  30 *1000, 500 );

        return $rv;
    }
    protected function setInvalidExtensions($invalid_extensions = "'exe,bat'")
    {
        $test = $this;

        $this->changeConfigValue('ban_extension', $invalid_extensions);
        $this->waitForPageLoaded( true );
    }

    protected function setKeyVersionNewFiles($v = 0)
    {
        sleep(1);
        $this->encryption_key_version_new_files = $v;
        $this->changeConfigValue('encryption_key_version_new_files', $v);
        $this->waitForPageLoaded( true );
    }
    
    static public function changeConfigValue($type, $value) {
        Logger::info("changeConfigValue type $type ");
        TestSuiteSupport::changeConfigValue($type, $value);
    }

    private function deleteDirectory($dir)
    {
        TestSuiteSupport::deleteDirectory($dir);
    }

    protected function checkDownloadUrl($url, $test_files_data)
    {
        $this->url($url);

        $this->assertEquals(1, preg_match('/token=([^&]*)?/', $url, $matches));
        $token = $matches[1];

        $elements = $this->elements($this->using('css selector')->value('.download.ui-button'));
        $count = count($elements);
        $this->assertTrue($count > 0);

        // we can't check actually downloading the file, but we can check if the download urls works?
        $data_ids = $this->individualDownloadsTest($token, $test_files_data);

        $zip = $this->downloadZip($token, $data_ids);
        $this->zipTest($zip, $test_files_data);
        unlink($zip);

        // set transfer to expired
        $recipient = Recipient::fromToken($token);
        $recipient->transfer->expires = strtotime('Yesterday');
        $recipient->transfer->save();

        $this->refresh();
        sleep(10);

        $elements = $this->elements($this->using('css selector')->value('.exception .message'));
        $count = count($elements);
        $this->assertEquals(1, $count);
        $this->assertContains('Transfer expired', $this->byCssSelector('.exception .message')->text());
    }

    public function downloadZip($token, $data_ids)
    {
        stream_context_set_default(array("ssl"=>array("allow_self_signed"=>true)));

        //https://file_sender.app/filesender/download.php?token=36c2120e-44b1-c06e-8c32-27e3c4285ee6&files_ids=
        $zip_location = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testzip-' . $token . '.zip';
        copy(Config::get('site_url') . "download.php?token=" . $token . "&files_ids=" . implode(',', $data_ids), $zip_location);

        return $zip_location;

    }

    private function zipTest($zip_location, $file_datas_to_check)
    {
        // unzip and check file contents

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zip_location), "Error reading zip-archive!");
        for($i = 0; $i < $zip->numFiles; $i++)
        {
            $fp = $zip->getStream($zip->getNameIndex($i));
            if(!$fp) exit("failed\n");
            while (!feof($fp)) {
                $file_data = stream_get_contents($fp);
                // do some stuff

                $file_key = array_search($file_data, $file_datas_to_check);
                $this->assertTrue($file_key !== false);
                unset($file_datas_to_check[$file_key]);
            }
            fclose($fp);
        }

        $this->assertEmpty($file_datas_to_check);
    }

    private function IndividualDownloadsTest($token, $file_datas_to_check)
    {
        stream_context_set_default(array("ssl"=>array("allow_self_signed"=>true)));

        $elements = $this->elements($this->using('css selector')->value('.files.box .file'));
        $data_ids = array();

        foreach($elements as $element)
        {
            $data_id = $element->attribute('data-id');
            $data_ids[] = $data_id;
            $file_data = file_get_contents(Config::get('site_url')."download.php?token=".$token."&files_ids=".$data_id);

            $file_key = array_search($file_data, $file_datas_to_check);
            $this->assertTrue($file_key !== false);
            unset($file_datas_to_check[$file_key]);
        }

        $this->assertEmpty($file_datas_to_check);


        return $data_ids;

    }

    private function IndividualEncryptedDownloadsTest($token, $file_datas_to_check)
    {
        stream_context_set_default(array("ssl"=>array("allow_self_signed"=>true)));
        // the download class is for encrypted direct downloads
        $elements = $this->elements($this->using('css selector')->value('.files.box .file.download'));
        $data_ids = array();

        foreach($elements as $element)
        {
            //  starts a download
            $element->click();

        }

        $this->assertEmpty($file_datas_to_check);


        return $data_ids;
    }

    protected function toggleAdvanceOption($name){
        $elements  = $this->elements($this->using('css selector')->value('[data-option="'.$name.'"]'));
        foreach($elements as $element){
            $element->click();
        }
    }

    protected function isCheckBoxSelected($css_selector) {
        $var = $this->execute(array('script' => "return document.querySelector('".$css_selector."').checked;", 'args' => array()));
        Logger::debug('The checkbox with selector '.$css_selector.', was it checked? '. gettype($var). ': '.($var?'y':'n'));
        return $var;
    }


    protected function clickCheckbox($css_selector) {
        $this->execute(array('script' => "document.querySelector('".$css_selector."').click();", 'args' => array()));
    }

    /**
     * Is the continue1 button able to be clicked on the first page of the upload process.
     */
    protected function canUploadPageStageXContinue( $stageNumber ) {

        $elements = $this->elements($this->using('css selector')->value('.btn.stage'.$stageNumber.'continue'));
        if( !count($elements)) {
            // Can not even find the button.
            $this->assertEquals( 1, count($elements), "upload page stage ".$stageNumber." continue button not found");
            return false;
        }
        
        $elements = $this->elements($this->using('css selector')->value('.btn.stage'.$stageNumber.'continue.disabled'));
        $r = count($elements);

        return !$r;
    }

    protected function canUploadPageStage1Continue() {
        return $this->canUploadPageStageXContinue('1');
    }
    protected function canUploadPageStage2Continue() {
        return $this->canUploadPageStageXContinue('2');
    }
    
    protected function assertUploadPageStage1Continue() {
        $this->assertEquals( $this->canUploadPageStage1Continue(), 1, "upload page stage1 continue button is disabled when it should not be" );
    }

    protected function assertUploadPageStage2Continue() {
        $this->assertEquals( $this->canUploadPageStage2Continue(), 1, "upload page stage2 continue button is disabled when it should not be" );
    }
    
    protected function uploadPageCountInvalidFiles( $reason ) {

        $elements = $this->elements($this->using('css selector')->value('*[class="file"]'));
        if( !count($elements)) {
//            $this->assertEquals( 1, count($elements), "upload page has no files added");
//            return 0;
        }

        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid ' . $reason . '"]'));
        $count = count($elements);
        echo "uploadPageCountInvalidFiles() $reason $count \n";
        return $count;       
    }

    protected function assertUploadPageNoFilesAreTooBig() {
        $this->assertEquals( 0,
                             $this->uploadPageCountInvalidFiles( 'transfer_maximum_size_exceeded' ),
                             "files should not be flagged for oversize" );
    }

    protected function isVisible( $e, $allowBlank = true ) {
        $s = $e->attribute("style");
        if( str_contains( $s, 'display: block' ) || str_contains( $s, 'display: table' )) {
            return true;
        }
        if( $allowBlank && !str_contains( $s, "display:")) {
            return true;
        }
        return false;
    }


    

    public function waitForCSS($cssSelector, $allowBlank = true) {
        $test = $this;

        $rv = $this->waitUntil(function() use ($test,$cssSelector,$allowBlank) {

            $e = $test->elements($test->using('css selector')->value($cssSelector));
            if( !count($e)) {
                Logger::info( "waiting for css to become available ".$cssSelector);
                return null;
            }
            if( !$this->isVisible( $e[0], $allowBlank )) {
                Logger::info( "css selector is not visible. ".$cssSelector);
                return null;
            }
            Logger::info( "css selector is ok. ".$cssSelector);
            return $e[0];
        },  10 *1000, 500 );

        return $rv;
    }

    public function waitForId($idSelector, $allowBlank = true) {
        $test = $this;

        $rv = $this->waitUntil(function() use ($test,$idSelector,$allowBlank) {

            $e = $test->elements($test->using('id selector')->value($idSelector));
            if( !count($e)) {
                Logger::info( "waiting for css to become available ".$idSelector);
                return null;
            }
            if( !$this->isVisible( $e[0], $allowBlank )) {
                Logger::info( "css selector is not visible. ".$idSelector);
                return null;
            }
            Logger::info( "css selector is ok. ".$idSelector);
            return true;
        },  10 *1000, 500 );

        return $rv;
    }
    
    


    public function waitForBootbox() {
        $test = $this;
        $cssSelector = '.bootbox';
        
        $rv = $this->waitUntil(function() use ($test,$cssSelector) {

            $e = $test->elements($test->using('css selector')->value($cssSelector));
            if( !count($e)) {
                Logger::info( "waiting for css to become available ".$cssSelector);
                return null;
            }
            if( !$this->isVisible( $e[0], false )) {
                Logger::info( "css selector is not visible. ".$cssSelector);
                return null;
            }
            Logger::info( "css selector is ok. ".$cssSelector);
            return true;
        },  60 * 1000, 500 );

        return $rv;
    }
    
    
    public function waitForStage($stage) {
        $test = $this;

        $this->waitUntil(function() use ($test,$stage) {

            // if we are waiting on stage 3 then the upload might have
            // completed before we get here and be at stage4 instead.
            if( $stage == 3 ) {
                $e = $test->elements($test->using('css selector')->value('.stage4'));
                if( $e ) {
                    if( $this->isVisible( $e[0] )) {
                        // stage 4 reached!
                        return true;
                    }
                }
            }

            $selector = '.stage'.$stage;
            $e = $test->elements($test->using('css selector')->value($selector));
            if( !count($e)) {
                Logger::info( "stage ".$stage."element can not be found ");
                return null;
            }
            if( !$this->isVisible( $e[0] )) {
                Logger::info( "stage ".$stage." element is not visible right now ");
                return null;
            }
            return true;
        },  10 *1000, 500 );
        
    }

    /**
     * Wait for stage X to be continuable and click the continue button and
     * wait for stage X+1 to be shown before returning.
     */
    public function stageXContinue($stage) {
        Logger::info("stageXContinue() stage $stage ");
            
        $this->waitForStage($stage);
        $this->byCssSelector('.btn.stage'.$stage.'continue')->click();
        $this->waitForStage($stage+1);
    }

    public function ensureTransferGetALink() {
        if( $e = $this->byId('galgal')) {
            $s = $e->attribute("style");
            Logger::info("style  $s ");
            if( $this->isVisible( $e )) {
                $e->click();
            }
        }            
    }
    public function ensureTransferByEmail() {
        if( $e = $this->byId('galemail')) {
            if( $this->isVisible( $e )) {
                $e->click();
            }
        }
    }
    


    protected function waitForUploadCompleteDialog( $urlExpected = true ) {
        $test = $this;
        
        $this->waitUntil(function() use ($test){

            $e = $test->elements($test->using('css selector')->value('.stage4'));
            if( !count($e)) {
                Logger::info( "stage element can not be found ");
                return null;
            }
            if( !$this->isVisible( $e[0] )) {
                Logger::info( "stage element is not visible right now ");
                return null;
            }                
            
            $elements = $test->elements($test->using('css selector')->value('.btn.mytransferslink'));
            $count = count($elements);
            Logger::info( "count is " . $count );
            if($count > 0)
            {
                return true;
            }
        }, 300 *1000, 500);


        $url = null;
        if( $urlExpected ) {
            $url = $test->byCssSelector('.downloadlink')->attribute('href');

            $this->assertGreaterThan( 20, strlen($url), "bad upload url" );
            $this->assertContains( '&token=', $url, "token not found in download url" );
            $this->assertRegExp('/token=[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $url );        
        }
        return $url;
    }
    
    protected function showFileUploader()
    {
        if (!$this->isCheckBoxSelected('[name="get_a_link"]')) {
            $this->clickCheckbox('[name="get_a_link"]');
        }
        
        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));
    }

    protected function addFile( $file_name )
    {
        $filepath = "unittests/selenium/assets/".$file_name;
//        echo "file_name $file_name \n";
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $filepath );
        return $filepath;
    }


    /**
     * Incomplete impl. $originalPath is available on the system but we can not monitor $filename
     * on a remote system using sauce labs.
     */
    public function downloadFile( $filename, $originalPath )
    {
        $test = $this;
        $filepath = $_SERVER['HOME'] . "/Downloads/" . $filename;
        if (file_exists( $filepath )) {
            unlink( $filepath );
        }
        
        sleep(2);
        Logger::info("downloadFile(t) $filename");
        $this->waitForCSS(".download_page");
        Logger::info("downloadFile(2) $filename");


        $file   = $this->byXPath('//tr[@data-name="'.$filename.'"]');
        $anchor = $this->byXPath('//tr[@data-name="'.$filename.'"]//a[contains(@class, "download")]');
        $dlprogress = $this->byXPath('//tr[@data-name="'.$filename.'"]//td[contains(@class, "downloadprogress")]');
        $anchor->click();

        // wait for the download
        $this->waitUntil(function() use ($test,$filepath,$filename,$originalPath) {

            if (!file_exists( $filepath )) {
                Logger::error("file does not exist $filepath");
                return null;
            }
            if( $originalPath ) {
                // still downloading?
                if( filesize($originalPath) != filesize($filepath)) {
                    Logger::error("file is wrong size $filepath");
                    return null;
                }
            }
            return true;
        },  20 *1000, 500 );
        

        Logger::info("downloadFile(near end) $originalPath ");
        if( $originalPath ) {
            $this->assertEquals( filesize($originalPath),
                                 filesize($filepath),
                                 "downloaded file is not the right size");
            $this->assertEquals( md5_file($originalPath),
                                 md5_file($filepath),
                                 "md5 of downloaded file does not match" );
            Logger::info("path $originalPath md5 " . md5_file($originalPath));
            Logger::info("path $filepath     md5 " . md5_file($filepath));
        }
        
        
/*
         // click download
        $this->byCss(".btn.download")->click();
        sleep(5);
        
        // set password
        $this->byCss(".ui-dialog-content.ui-widget-content .wide")->value("123123");
        
        // click ok
        $this->byCss(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button")->click();
        $this->waitForAndEnsureCssElementContains(".downloadprogress", 'Download complete');
  */      

    }

    protected function accordionOpen( $id )
    {
        $this->byXPath( $id )->click();
    }


    private function checkCheckbox($name, $checked = true) {
        if ($checked != $this->isCheckBoxSelected('[name="'.$name.'"]')) {
            $this->clickCheckbox('[name="'.$name.'"]');
        }
    }
    
    
    protected function ensureOptions( $opts )
    {
        foreach ($opts as $k => $v) {
            $this->checkCheckBox( $k, $v );
        }
    }

    protected function uploadPageStage2ShowAdvancedOptions() {
        $this->accordionOpen('//button[@data-bs-target="#collapseOne"]');
    }

    /**
     * If $count is zero and the css selector finds nothing then we return ok
     * This is to be used when you have already checked that positive elements
     * exist and want to assert that there are no negative ones.
     */
    public function waitForElementCount($cssSelector,$count) {
        $test = $this;

        $rc = $this->waitUntil(function() use ($test,$cssSelector,$count) {

            $e = $test->elements($test->using('css selector')->value($cssSelector));
            if( !count($e)) {
                if( !$count ) {
                    return true;
                }
                Logger::info( "css selector ".$cssSelector." element can not be found count ".$count);
                return null;
            }
            if( !$this->isVisible( $e[0] )) {
                Logger::info( "css selector ".$cssSelector." element is not visible right now ");
                return null;
            }
            if( count($e) < $count ) {
                Logger::info( "css selector ".$cssSelector." too few visible elements right now ");
                return null;
            }
            
            return true;
        },  10 *1000, 500 );

        return $rc;
    }


    public function waitForFilesListWithPossibleError($cssSelector,$number_of_files,$error_expected) {
    
        if( $error_expected ) {
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file.invalid'.$cssSelector, 1 ),
                                 "css error selector message must be shown for a file" );
        } else {
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file',$number_of_files),
                                 "all the files are shown in a list" );
            $this->assertEquals( true,
                                 $this->waitForElementCount('.file.invalid'.$cssSelector, 0 ),
                                 "css error must not be shown for any file" );
        }
    }

    /**
     *  This asserts that the stage1 continue button is on the page and is visible
     *  if $disabled_expected is true then the continue button must also not be clickable.
     */
    public function assertStage1ContinueDisabled( $disabled_expected )
    {
        // stage1continue disabled
        $elements = $this->elements($this->using('css selector')->value('.stage1continue'));
        $this->assertEquals( 1, count($elements),
                             "stage 1 continue button expected but not found" );
        $this->assertEquals( true, $this->isVisible( $elements[0] ),
                             "stage 1 continue button expected to be visible but not" );

        $v = $elements[0]->attribute("class");

        $expected = $disabled_expected > 0;
        $this->assertEquals( $expected, str_contains( $v, 'disabled' ),
                             "stage 1 continue disabled state not what is expected" );
    }

    public function scrollToTop()
    {
        $this->sendKeys($this->byCssSelector(".core"), Keys::HOME);
        sleep(2);
    }

    public function scrollToBottom()
    {
        $this->sendKeys($this->byCssSelector(".core"), Keys::END);
        sleep(2);
        $this->sendKeys($this->byCssSelector(".core"), Keys::END);
        sleep(2);
    }

    public function scrollIntoView( $selector )
    {
        $this->execute(array('script' => "document.querySelector(\"".$selector."\").scrollIntoView(true);",
                             'args' => array()));
    }
    
    public function removeUploadStage1( $name )
    {
        $element = $this->waitForCSS("tr[data-name='".$name."'] .removebutton");
        $element->click();
    }
    
}
