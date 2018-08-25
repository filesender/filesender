<?php

require_once 'unittests/selenium/SeleniumTest.php';

class HeaderMenuTest extends SeleniumTest
{

    protected $start_url_path = '';
    

    public function testHeaderMenu()
    {
        extract($this->getKeyBindings());

        $this->setupUnauthenticated();

        $this->assertEquals(0, $this->getHeaderMenuSize());

        $this->setupAuthenticated();

        $this->assertEquals(3, $this->getHeaderMenuSize());

        $this->setUserPage();

        $this->assertEquals(4, $this->getHeaderMenuSize());

        $this->setAdmin();

        $this->assertEquals(5, $this->getHeaderMenuSize());

        $this->unsetAdmin();

        $this->assertEquals(4, $this->getHeaderMenuSize());

        $this->unsetUserPage();

        $this->assertEquals(3, $this->getHeaderMenuSize());

        $this->setupUnauthenticated();

        $this->assertEquals(0, $this->getHeaderMenuSize());
    }

    private function getHeaderMenuSize()
    {
        $elements = $this->elements($this->using('css selector')->value('.leftmenu ul li'));
        $count = count($elements);

        return $count;
    }

}
