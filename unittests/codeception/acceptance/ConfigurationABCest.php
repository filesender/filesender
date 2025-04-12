<?php


namespace Tests\Acceptance;


include_once(FILESENDER_BASE . "/vendor/autoload.php");
include_once(FILESENDER_BASE . '/classes/utils/TestSuiteSupport.class.php');

use Tests\Support\AcceptanceTester;
use \Tests\Support\Page\Acceptance\FileSender;


class ConfigurationABCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    
    private $settingsA = array();
    private $settingsB = array();

    private function applySettings( $I, $fs, $s )
    {
        $fs->clearCustomConfig();
        foreach( $s as $k => $v ) {
            $fs->setConfig($k,$v);
        }
        $fs->writeCustomConfig();
    }
    
    private function switchToConfigurationA( $I, $fs )
    {
        $I->amOnPage('/');
        $this->applySettings($I, $fs, $this->settingsA);
        $fs->setupAuth();        
        $I->amOnPage('/');
    }

    private function switchToConfigurationB( $I, $fs )
    {        
        $I->amOnPage('/');
        $this->applySettings($I, $fs, $this->settingsB);
        $fs->setupAuth();        
        $I->amOnPage('/');
/*        
        $fs->clearCustomConfig();

        $v = 32 * 1024 * 1024;
        $fs->setConfig('upload_chunk_size', $v );
        $fs->setConfig('upload_crypted_chunk_size', 32 + $v );
        $fs->setConfig('upload_chunk_size', $v );

        $fs->writeCustomConfig();
 */
        
    }
    
    
    private function runtestsAB( AcceptanceTester $I, FileSender $fs, $settingsA, $settingsB )
    {
        $this->I = $I;
        $this->fs = $fs;

        $this->settingsA = $settingsA;        
        $this->settingsB = $settingsB;


        // upload in configA, download in configB
        // upload in configB, download in configA
        ////////////////////////////////
        ////////////////////////////////
        ////////////////////////////////        
        $this->switchToConfigurationA( $I, $fs );
        $fs->uploadNotEncrypted(array("file2.txt", "file10mb.txt"));
 
        $this->switchToConfigurationB( $I, $fs );
        $fs->downloadNotEncrypted();
        $fs->downloadNotEncrypted("file10mb.txt");
        $I->amOnPage('/');
        $fs->uploadNotEncrypted(array("file2.txt", "file10mb.txt"));

        $this->switchToConfigurationA( $I, $fs );
        $I->amOnPage('/');
        $fs->downloadNotEncrypted();
        $fs->downloadNotEncrypted("file10mb.txt");

        ////////////////////////////////
        ////////////////////////////////
        ////////////////////////////////
        

        $this->switchToConfigurationA( $I, $fs );
        $fs->uploadEncrypted();
        
        $this->switchToConfigurationB( $I, $fs );
        $fs->downloadEncrypted();
        $I->amOnPage('/');
        $fs->uploadEncrypted();

        $this->switchToConfigurationA( $I, $fs );
        $fs->downloadEncrypted();

        ////////////////////////////////
        ////////////////////////////////
        ////////////////////////////////
        

        $this->switchToConfigurationA( $I, $fs );
        $fs->uploadEncrypted(array("file2.txt", "file10mb.txt"));
 
        $this->switchToConfigurationB( $I, $fs );
        $fs->downloadEncryptedArchive( array("file10mb.txt", "file2.txt", "file20mb.txt" ));
        $I->amOnPage('/');
        $fs->uploadEncrypted(array("file2.txt", "file10mb.txt"));
        
        $this->switchToConfigurationA( $I, $fs );
        $fs->downloadEncryptedArchive( array("file10mb.txt", "file2.txt", "file20mb.txt" ));

        
    }    

    public function testConfigurationAB( AcceptanceTester $I, FileSender $fs )
    {
        // explicitly set the chunk size to 5mb and then 32mb
        // as the A and B setup
        $smallv =  5 * 1024 * 1024;
        $v      =  7 * 1024 * 1024;  // this shouldn't line up well with 5

        $this->runtestsAB( $I, $fs,
                           array(
                               'storage_type' => 'filesystem',
                               'upload_chunk_size' => $smallv,
                               'upload_crypted_chunk_size' => 32 + $smallv,
                               'download_chunk_size' => $smallv,
                           ),
                           array(
                               'storage_type' => 'filesystem',
                               'upload_chunk_size' => $v,
                               'upload_crypted_chunk_size' => 32 + $v,
                               'download_chunk_size' => $v,
                           ));

        $this->runtestsAB( $I, $fs,
                           array(
                               'storage_type' => 'filesystemChunked',
                               'upload_chunk_size' => $smallv,
                               'upload_crypted_chunk_size' => 32 + $smallv,
                               'download_chunk_size' => $smallv,
                           ),
                           array(
                               'storage_type' => 'filesystemChunked',
                               'upload_chunk_size' => $v,
                               'upload_crypted_chunk_size' => 32 + $v,
                               'download_chunk_size' => $v,
                           ));
 
        
    }
    
}
