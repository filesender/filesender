<?php

require_once 'unittests/selenium/SeleniumTest.php';

class HeaderMenuTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testHeaderMenu()
    {
        extract($this->getKeyBindings());

        $this->setupUnauthenticated();
        sleep(1);

        $this->assertEquals(0, $this->getHeaderMenuSize());

        $this->setupAuthenticated();

        $this->assertGreaterThanOrEqual( 3, $this->getHeaderMenuSize(),
                                         'authenticated user menu item count' );

        $this->setUserPage();

        $this->assertGreaterThanOrEqual(4, $this->getHeaderMenuSize(),
                                        'user page' );

        $this->setAdmin();

        $this->assertEquals(5, $this->getHeaderMenuSize());

        $this->unsetAdmin();

        $this->assertGreaterThanOrEqual( 4, $this->getHeaderMenuSize());

        $this->unsetUserPage();

        $this->assertEquals(3, $this->getHeaderMenuSize());

        $this->setupUnauthenticated();
        sleep(1);
        

        $this->assertEquals(0, $this->getHeaderMenuSize());

        $this->setupAuthenticated();
        sleep(1);
    }

    private function getHeaderMenuSize()
    {
        $elements = $this->elements($this->using('css selector')->value('.leftmenu ul li'));
        $count = count($elements);

        return $count;
    }

}
