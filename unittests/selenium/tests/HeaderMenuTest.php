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
        $this->assertEquals(4, $this->getHeaderMenuSize());

        $this->setupAuthenticated();
        sleep(1);
        $this->assertGreaterThanOrEqual( 3, $this->getHeaderMenuSize(),
                                         'authenticated user menu item count' );

        $this->setUserPage();

        $this->assertGreaterThanOrEqual(4, $this->getHeaderMenuSize(),
                                        'user page' );

        $this->setAdmin();

        $this->assertEquals(10, $this->getHeaderMenuSize());

        $this->unsetAdmin();

        $this->assertGreaterThanOrEqual( 4, $this->getHeaderMenuSize());

        $this->unsetUserPage();
        
        $this->assertEquals(8, $this->getHeaderMenuSize());

        
        $this->setupUnauthenticated();
        $this->assertEquals(4, $this->getHeaderMenuSize());

        
    }

    private function getHeaderMenuSize()
    {
        $elements = $this->elements($this->using('css selector')->value('#navbarFilesender > .nav-item'));
        $count = count($elements);
        return $count;
    }

    public function tearDown()
    {
        $this->setupAuthenticated();
        sleep(1);
    }
    
}
