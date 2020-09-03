<?php

require_once 'unittests/selenium/SeleniumTest.php';

class LanguageSelectTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testLanguageSelect()
    {
        extract($this->getKeyBindings());

        $this->setupUnauthenticated();

        for ($second = 0; ; $second++) {
            if ($second >= 60) $this->fail("timeout");
            try {
                if ($this->byId("language_selector")!=null  ? true : false) break;

            } catch (Exception $e) {}
            sleep(1);
        }

        $this->select($this->byId("language_selector"))->selectOptionByLabel("nl-nl");

        $this->assertContains('FileSender is een veilige manier om bestanden te delen met iedereen!', $this->byCssSelector("#page .box")->text());


        $this->select($this->byId("language_selector"))->selectOptionByLabel("English (US)");

        $this->assertContains('FileSender is a secure way to share large files with anyone!', $this->byCssSelector("#page .box p")->text());
    }

}
