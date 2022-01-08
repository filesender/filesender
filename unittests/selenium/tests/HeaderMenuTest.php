<?php

require_once 'unittests/selenium/SeleniumTest.php';

class HeaderMenuTest extends SeleniumTest
{

    protected $start_url_path = '';

    public function testHeaderMenuAuthenticated()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        $this->assertGreaterThanOrEqual( 3, $this->getHeaderMenuSize(),
                                         'authenticated user menu item count' );

        $this->setUserPage();
    }
    
    public function testHeaderMenuAuthenticatedUserPage()
    {
        extract($this->getKeyBindings());
        $this->assertGreaterThanOrEqual(4, $this->getHeaderMenuSize(),
                                        'user page' );

        $this->setAdmin();
    }

    
    public function testHeaderMenuAdminPage()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        sleep(3);
        
        $this->assertEquals(10, $this->getHeaderMenuSize());
        
        $this->unsetAdmin();
    }
    
    public function testHeaderMenuNoAdminPage()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();
        
        $this->assertGreaterThanOrEqual( 4, $this->getHeaderMenuSize());
        
        $this->unsetUserPage();
    }
    
    public function testNormalHeader()
    {
        extract($this->getKeyBindings());
        $this->setupAuthenticated();

        $this->assertEquals(8, $this->getHeaderMenuSize());
        
        $this->setupUnauthenticated();
    }
    
    public function testNormalUnauthenticated()
    {
        extract($this->getKeyBindings());
        $this->assertEquals(4, $this->getHeaderMenuSize());
        $this->setupAuthenticated();
        sleep(1);
    }


    private function getHeaderMenuSize()
    {
        $elements = $this->elements($this->using('css selector')->value('#navbarFilesender > .nav-item'));
        $count = count($elements);
        return $count;
    }

    
}
