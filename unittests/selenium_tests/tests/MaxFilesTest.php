<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class MaxFilesTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testEnoughFiles()
    {
        $this->fileQuantityTest(30, false);

    }


    public function testTooMuchFiles()
    {
        $this->fileQuantityTest(31, true);
    }

    private function fileQuantityTest($number_of_files, $error_expected)
    {
        extract($this->getKeyBindings());

        $this->setupAuthenticated();

        ${"temp"} = $this->execute(array(  'script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args'   => array() ));

        $test_files_created = array();
        for($i = 0; $i < $number_of_files; $i++)
        {
            $test_file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'no'.($i+1).'.txt';
            $test_files_created[] = $test_file;
            copy("unittests/selenium_tests/assets/124bytes.txt", $test_file);
            $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test_file);
        }


        $elements = $this->elements($this->using('css selector')->value('*[class="file invalid transfer_too_many_files"]'));
        $count = count($elements);

        $this->assertEquals($error_expected?1:0, $count);

        foreach($test_files_created as $test_file)
        {
            unlink($test_file);
        }
    }



}
