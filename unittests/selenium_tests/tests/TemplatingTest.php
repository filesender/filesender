<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class TemplatingTest extends SeleniumTest
{

    protected $start_url_path = '';

    protected $skin_folder = 'www/skin';
    protected $css_file = 'styles.css';
    protected $javascript_file = 'script.js';

    protected $skin_folder_created = false;
    protected $javascript_exists = false;
    protected $css_exists = false;

    protected $teardown_function = null;


    public function testCss()
    {
        $this->setupUnauthenticated();

        if(file_exists($this->skin_folder))
        {
            if(file_exists($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file))
            {
                $this->css_exists = true;
                rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file, $this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file.'_old');
            }
        } else {
            $this->skin_folder_created = true;
            mkdir($this->skin_folder);
        }

        $current_background_color = $this->byCssSelector('body')->css('background-color');
//        echo 'CssTest: Current background color found: '.$current_background_color."\n";

        // invert
        $new_background_color = preg_replace_callback('/rgba\((\d{1,3}), (\d{1,3}), (\d{1,3}), (.*)\)/', function($matches){
            return 'rgba('.(255-$matches[1]).', '.(255-$matches[2]).', '.(255-$matches[3]).', '.$matches[4].')';
        }, $current_background_color);


//        echo 'CssTest: New background color to check: '.$new_background_color."\n";


        // put in file
        file_put_contents($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file, 'body { background-color: '.$new_background_color.'!important}');

        $this->refresh();

        $this->assertEquals($this->byCssSelector('body')->css('background-color'), $new_background_color);

        $this->teardown_function = array($this, 'tearDownCss');
    }

    public function testJavascript()
    {
        $this->setupUnauthenticated();

        if(file_exists($this->skin_folder))
        {
            if(file_exists($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file))
            {
                $this->javascript_exists = true;
                rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file, $this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file.'_old');
            }
        } else {
            mkdir($this->skin_folder);
            $this->skin_folder_created = true;
        }

        $test_div_id = 'test-div-'.$this->generateRandomString();
        $test_div_message = 'This is a test div';

        file_put_contents($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file, 'document.write("<div id=\''.$test_div_id.'\'>'.$test_div_message.'</div>");');

        $this->refresh();

        $this->assertEquals($this->byCssSelector('#'.$test_div_id)->text(), $test_div_message);

        $this->teardown_function = array($this, 'tearDownJavascript');
    }

    public function tearDown()
    {
        if($this->teardown_function !== null)
        {
            call_user_func($this->teardown_function);
        }

        if($this->skin_folder_created)
        {
            rmdir($this->skin_folder);
        }


        parent::tearDown();
    }


    public function tearDownCss()
    {
        unlink($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file);
        if($this->css_exists)
        {
            rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file.'_old', $this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file);
        }
    }

    public function tearDownJavascript()
    {
        unlink($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file);
        if($this->javascript_exists)
        {
            rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file.'_old', $this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file);
        }
    }

    /**
     * Found at http://stackoverflow.com/a/4356295/2591190
     * @param int $length
     * @return string
     */
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
