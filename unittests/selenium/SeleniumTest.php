<?php

include_once(dirname(__FILE__) . '/../../vendor/autoload.php');
include_once(dirname(__FILE__) . '/../../classes/utils/TestSuiteSupport.class.php');

class SeleniumTest 
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

//         parent::setUp();
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

//        return parent::browsers();
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


//        parent::__construct($name, $data, $dataName);
        
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

//        return parent::setUpPage();
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

//        parent::tearDown();
    }

    protected function setupUnauthenticated()
    {
        $this->changeConfigValue('auth_sp_type', "'saml'");
        sleep(2);
        $this->refresh();
        sleep(5);
        $this->authType = 'saml';
    }

    protected function setupAuthenticated()
    {
        if( $this->authType == 'fake' ) {
            return;
        }
        $this->changeConfigValue('auth_sp_type', "'fake'");
        sleep(2);
        $this->refresh();
        sleep(2);
        $this->authType = 'fake';
    }

    protected function setAdmin()
    {
        $this->changeConfigValue('admin', "'1'");
        sleep(2);
        $this->refresh();
        sleep(2);
    }

    protected function unsetAdmin()
    {
        $this->changeConfigValue('admin', "'0'");
        sleep(2);
        $this->refresh();
        sleep(2);
    }

    protected function setUserPage()
    {
        $this->changeConfigValue('user_page',
                                 'array(\'lang\'=>true,\'auth_secret\'=>true,\'id\'=>true,\'created\'=>true)');
        sleep(2);
        $this->refresh();
        sleep(2);
    }

    protected function unsetUserPage()
    {
        $this->changeConfigValue('user_page',
                                 'array()');
        sleep(2);
        $this->refresh();
        sleep(2);
    }

    protected function setMaxTransferFileSize($max_file_size = 2107374182400)
    {
        $this->changeConfigValue('max_transfer_size',
                                 $max_file_size);
        sleep(2);
        $this->refresh();
        sleep(2);
    }



    protected function setInvalidExtensions($invalid_extensions = "'exe,bat'")
    {
        $this->changeConfigValue('ban_extension',
                                 $invalid_extensions);
        sleep(2);
        $this->refresh();
        sleep(2);
    }

    protected function setKeyVersionNewFiles($v = 0)
    {
        sleep(1);
        $this->encryption_key_version_new_files = $v;
        $this->changeConfigValue('encryption_key_version_new_files', $v);
        $this->refresh();
        sleep(2);
    }
    
    public function changeConfigValue($type, $value) {
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

    /**
     * Download a zip given the token and the array of file ids to include.
     */
    public function downloadZip($token, $data_ids)
    {
        stream_context_set_default(array("ssl"=>array("allow_self_signed"=>true)));

        //https://file_sender.app/filesender/download.php?token=36c2120e-44b1-c06e-8c32-27e3c4285ee6&files_ids=
        $zip_location = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testzip-' . $token . '.zip';
        copy(Config::get('site_url') . "download.php?token=" . $token . "&files_ids=" . implode(',', $data_ids), $zip_location);

        return $zip_location;

    }

    /**
     * unzip and check file contents
     * @param file_datas_to_check is an array of file contents that should be found inside the zip at zip_location.
     *        each file contents can only match a file once.
     */
    private function zipTest($zip_location, $file_datas_to_check)
    {

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

}
