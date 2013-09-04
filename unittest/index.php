<?php
require_once('../simpletest/autorun.php');;

class testAll extends TestSuite {
    function testAll() {
        $this->TestSuite('All tests');
		$this->addFile('configUT.php');
        $this->addFile('coreUT.php');
		$this->addFile('voucherUT.php');
    }
}
?>