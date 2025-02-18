<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;

class HeaderMenuCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }


    
    public function testHeaderMenu( AcceptanceTester $I, FileSender $fs )
    {
        $headerMenuSelector = '.leftmenu ul li';
        
        $I->amOnPage('/');
        $I->wait(1);
        
        $fs->ensureLogoff();
        $I->seeNumberOfElements($headerMenuSelector, 0);
        
        $fs->ensureLogon();
        $I->wait(1);
        $I->seeNumberOfElements($headerMenuSelector, [3,20]);

        $fs->setUserPage();
        $I->seeNumberOfElements($headerMenuSelector, [4,20]);

        $fs->setAdmin(1);
        $I->seeNumberOfElements($headerMenuSelector, [5,20]);

        $fs->setAdmin(0);
        $I->seeNumberOfElements($headerMenuSelector, [5,20]);
                
        $fs->ensureLogoff();
        $I->seeNumberOfElements($headerMenuSelector, 0);
    }

    
}
