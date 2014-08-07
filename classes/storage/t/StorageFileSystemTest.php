<?php

/**
 *  Tests the StorageFileSystem class to make sure that its methods work
 *  as intended: take known input and output expected results
 *
 */
class StorageFileSystemTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // sets up needed objects and properties - very simple way
        
        $this->dbfile = new File();
        $this->dbfile->name = "0.tmp"; // Hopefully unused filename in FILESENDER_BASE/tmp
        $this->storefilesys = StorageFileSystem::getInstance();
        $this->str_chunk = "abcdefghijklmnopqrstuvwxyz";
        $this->offset = 0;

        //Needed configs
        $this->confs = array(
            'file_location' => Config::get('filestorage_filesystem_file_location'),
            'temp_location' => Config::get('filestorage_filesystem_temp_location'),
            'chunk_size' => Config::get('upload_chunk_size'),
            'calc_hash' => Config::get('filestorage_filesystem_calc_hash'),
        );
    }

    public function testInstance()
    {
        $this->assertNotEmpty($this->storefilesys);
        $this->assertEquals($this->confs['file_location'], $this->storefilesys->uploadfolder);
        $this->assertEquals($this->confs['temp_location'], $this->storefilesys->tempfolder);
        $this->assertEquals($this->confs['chunk_size'], $this->storefilesys->chunksize);
        $this->assertEquals($this->confs['calc_hash'], $this->storefilesys->calculate_hash);
    }


    /** 
     *  Runs writeChunk method, checks if what was written (a string for now)
     *  equals to what it was supposed to write - alternatively a hash checksum
     *  can be calculated of the file written and compared to checksum of file
     *  to write OR just use assertFileEquals(file1, file2)
     *  
     *  For now it just writes a predefined string and testRead() compares with it
     *
     *  @depends testInstance
     */
    public function testWrite()
    {
        $written = $this->storefilesys->writeChunk($this->dbfile, $this->str_chunk, $this->offset);
        $this->assertTrue($written);

        $this->filesys_file = $this->storefilesys->uploadfolder.$this->dbfile->name; 
        $this->assertFileExists($this->storefilesys->uploadfolder.$this->dbfile->name);
    }


    /**
     *  This one uses the stuff written to file in testWrite(),
     *  reads it and compares it to a predefined test constant.
     *
     *  @depends testWrite
     */
    public function testRead()
    {
        $sysfile = $this->storefilesys->uploadfolder.$this->dbfile->name;
        $read = $this->storefilesys->readChunk($sysfile);
        $this->assertEquals($this->str_chunk, $read);
    }


    /**
     *  Tests that StorageFilesystem->delete() really deletes a file from disk
     *  Originally this was the "clean-up" method tearDown()
     *
     *  @depends testWrite
     */
    public function testDelete()
    {
        $sysfile = $this->storefilesys->uploadfolder.$this->dbfile->name;
        $this->storefilesys->delete($sysfile);
        $this->assertFileNotExists($sysfile);
    }
}

