<?php

require_once 'unittests/selenium/SeleniumTest.php';

class LanguageSelectTest extends SeleniumTest
{

    
    private function selectLangauge( $lang )
    {
        sleep(10);
        
        $this->waitForCSS(".language-dropdown-toggle")->click();
        sleep(2);
        $this->scrollIntoView( "#toplangdropdown > a[data-id='".$lang."']" );
//        $this->execute(array('script' => "document.querySelector(\"#toplangdropdown > a[data-id='".$lang."']\").scrollIntoView(true);",
//                             'args' => array()));
        sleep(5);
        
        $this->waitForCSS("#toplangdropdown > a[data-id='".$lang."']")->click();
    }

    private function pageText()
    {
        return $this->byCssSelector("#page .core p")->text();
    }
        
    public function testLanguageSelect()
    {
        extract($this->getKeyBindings());
        $this->setupUnauthenticated();

        $this->selectLangauge( 'nl-nl' );
        $this->assertContains( 'FileSender is een veilige manier om bestanden te delen met iedereen!',
                               $this->pageText());

        $this->selectLangauge( 'en-us' );
        $this->assertContains( 'FileSender is a secure way to share large files with anyone!',
                               $this->pageText());

    }

}
