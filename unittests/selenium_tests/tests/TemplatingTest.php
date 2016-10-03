<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class TemplatingTest extends SeleniumTest
{

    protected $start_url_path = '';

    protected $skin_folder = '../www/skin';
    protected $css_file = 'styles.css';
    protected $javascript_file = 'script.js';

    protected $skin_folder_created = false;
    protected $javascript_exists = false;
    protected $css_exists = false;


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

        $current_background_color = $this->byCssSelector('body')->getCssValue('backgound-color');
        echo 'CssTest: Current background color found: '.$current_background_color."\n";

        // invert
        $new_background_color = $this->color_inverse($current_background_color);
        echo 'CssTest: New background color to check: '.$new_background_color."\n";


        // put in file
        file_put_contents($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file, 'body { background-color: '.$new_background_color.'!important}');

        $this->refresh();

        $this->waitUntil(function() use ($new_background_color){
            echo 'CssTest: new background color found: '.$this->byCssSelector('body')->getCssValue('backgound-color')."\n";
            return $this->assertEquals($this->byCssSelector('body')->getCssValue('backgound-color'), $new_background_color);
        }, 2000);
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

        $this->waitUntil(function() use ($test_div_id, $test_div_message){
            echo 'Javascript test: Content of div: '.$test_div_id.' is: '. $test_div_message."\n";
            return $this->assertEquals($this->byCssSelector('#'.$test_div_id)->text(), $test_div_message);
        }, 2000);

    }


    public function tearDown()
    {
        unlink($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file);
        if($this->css_exists)
        {
            rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file.'_old', $this->skin_folder.DIRECTORY_SEPARATOR.$this->css_file);
        }


        unlink($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file);
        if($this->javascript_exists)
        {
            rename($this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file.'_old', $this->skin_folder.DIRECTORY_SEPARATOR.$this->javascript_file);
        }

        if($this->skin_folder_created)
        {
            unlink($this->skin_folder);
        }

        parent::tearDown();
    }

    /**
     * Found at: http://www.jonasjohn.de/snippets/php/color-inverse.htm
     * @param $color
     * @return string
     */
    private function color_inverse($color) {
        $color = str_replace('#', '', $color);
        if (strlen($color) != 6){ return '000000'; }
        $rgb = '';
        for ($x=0;$x<3;$x++){
            $c = 255 - hexdec(substr($color,(2*$x),2));
            $c = ($c < 0) ? 0 : dechex($c);
            $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
        }
        return '#'.$rgb;
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
