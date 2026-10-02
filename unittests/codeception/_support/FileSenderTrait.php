namespace Codeception;

trait FileSenderTrait {

    public function setupAuth(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Share your files safely');
        
        $I->click('#btn_logon');
        $I->see('Enter your username and password');
        $I->fillField('username', 'testdriver@localhost.localdomain');
        $I->fillField('password', 'hello');
        $I->click('#submit_button');
        $I->see('Get a link instead of sending to recipients');
    }


    private function waitForDownloads(AcceptanceTester $I)
    {
        $limit = 30;

        for( $i = 0; $i < $limit; $i++ ) {
            
            $v =  $I->executeJS(<<<EOT

            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList').items;
console.log(v);


            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList')
	    .items.filter(e => e.state != '2')
.length;console.log('AA');
console.log(v);
            if( v > 0 ) {
                return false;
            }
            var v = document.querySelector('downloads-manager')
            .shadowRoot.querySelector('#downloadsList').items;
console.log('BB');
console.log(v);
return v[0].filePath;
EOT );


            if( $v != '' ) {
	        $I->closeTab();
                return $v;
            }
            $I->wait(1);
        }
	$I->closeTab();
    }

}
