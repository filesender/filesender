<?php

require_once 'unittests/selenium/SeleniumTest.php';

class InvalidExtensionsTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testBat()
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        $this->extensionTest('bat', true);
    }


    public function testExe()
    {
        $this->extensionTest('exe', true);
    }

    public function testAllowedExe()
    {
        $this->setInvalidExtensions("'somethingelse,somethingother'");

        $this->extensionTest('exe', false);

    }

    public function testInvalidTestExtension()
    {
        $invalid_file_extension = 'invalidfileextension'.rand(0,100);

        $this->setInvalidExtensions("'exe,bat,$invalid_file_extension'");

        $this->extensionTest($invalid_file_extension, true);
    }

    private function extensionTest($file_extension, $ban_expected)
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        sleep(1);
        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));

        $test_file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'invalidextension.'.$file_extension;
        copy("unittests/selenium/assets/124bytes.txt", $test_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test_file);

        sleep(1);
        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid banned_extension"]'));
        $count = count($elements);

        $this->assertEquals($ban_expected?1:0, $count);


        //reset
        $this->setInvalidExtensions();
    }


}
