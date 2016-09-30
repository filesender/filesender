<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class LanguageSelectTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testLanguageSelect()
    {
        extract($this->getKeyBindings());

        for ($second = 0; ; $second++) {
            if ($second >= 60) $this->fail("timeout");
            try {
                if ($this->byId("language_selector")!=null  ? true : false) break;

            } catch (Exception $e) {}
            sleep(1);
        }

        $this->select($this->byId("language_selector"))->selectOptionByLabel("nl-nl");

        $this->waitUntil(function(){
            echo '1: '.$this->byCssSelector("#page .box")->text()."\n";
            return $this->assertEquals(strpos($this->byCssSelector("#page .box")->text(), 'FileSender is een veilige manier om bestanden te delen met iedereen!') !== false, true);
        }, 2000);



        $this->select($this->byId("language_selector"))->selectOptionByLabel("English (US)");
        $this->waitUntil(function(){
            echo '2: '.$this->byCssSelector("#page .box p")->text()."\n";
            return $this->assertEquals(strpos($this->byCssSelector("#page .box p")->text(), 'FileSender is a secure way to share large files with anyone !') !== false, true);
        }, 2000);

    }

}
