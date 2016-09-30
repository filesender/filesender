<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class LanguageSelectTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testLogin()
    {
        extract($this->getKeyBindings());

        sleep(400);

        for ($second = 0; ; $second++) {
            if ($second >= 60) $this->fail("timeout");
            try {
                if ($this->byId("language_selector")!=null  ? true : false) break;

            } catch (Exception $e) {}
            sleep(1);
        }

        $this->select($this->byId("language_selector"))->selectOptionByLabel("nl-nl");

        $this->waitUntil(function(){
            return $this->assertEquals($this->byCssSelector("#page .box"), '     Welkom bij FileSender  FileSender is een veilige manier om bestanden te delen met iedereen! Meld u aan om een bestand te versturen of om iemand uit te nodigen om een bestand te sturen.          ');
        }, 2000);

        $this->select($this->byId("language_selector"))->selectOptionByLabel("English (US)");
        $this->waitUntil(function(){
            return $this->assertEquals($this->byCssSelector("#page .box p"), '     FileSender is a secure way to share large files with anyone !     Logon to upload your files or invite people to send you a file. ');
        }, 2000);

    }

}
