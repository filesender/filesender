<?php

require_once 'unittests/selenium/BasisTest.php';

class HeaderMenuTest extends BasisTest
{

    protected $start_url_path = '';
    

    public function testHeaderMenu()
    {
        // $this->unsetAdmin();
        // sleep(1);
        // $this->unsetUserPage();
        // sleep(1);
        // $this->setupUnauthenticated();
        // sleep(1);


        extract($this->getKeyBindings());

        $this->setupUnauthenticated();
        sleep(5);

        $this->assertEquals(0, $this->getHeaderMenuSize());

        $this->setupAuthenticated();
        sleep(5);

        $this->assertGreaterThanOrEqual( 3, $this->getHeaderMenuSize(),
                                         'authenticated user menu item count' );

        $this->setUserPage();
        sleep(5);

        $this->assertGreaterThanOrEqual(4, $this->getHeaderMenuSize(),
                                        'user page' );

        $this->setAdmin();
        sleep(5);
        $this->assertEquals(5, $this->getHeaderMenuSize());

        $this->unsetAdmin();
        sleep(5);

        $this->assertGreaterThanOrEqual( 4, $this->getHeaderMenuSize());

        $this->unsetUserPage();
        sleep(5);

        $this->assertEquals(3, $this->getHeaderMenuSize());

        $this->setupUnauthenticated();
        sleep(5);
        

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
