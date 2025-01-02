<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class LanguageSelectCest
{

    public function testLanguageSelect( AcceptanceTester $I, FileSender $fs )
    {
        $fs->setupAuth();

        $I->retrySeeElement('#language_selector');

        $I->selectOption("#language_selector", "Dutch");
        
        
        $I->see('Sleep of kies');

        $I->selectOption("#language_selector", "English (AU)");

        $I->see('drag & drop your files here');

    }
    
}
