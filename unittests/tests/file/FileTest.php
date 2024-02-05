<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(__FILE__) . '/../common/CommonDatabaseTestCase.php';

/**
 * File test class
 * 
 * @backupGlobals disabled
 */
class FileTest extends CommonDatabaseTestCase {
    /*
     * Some variables used in tests case
     */

    private $transferSubject;    // Default subject of tranfer
    private $transferMessage;    // Default message of transfer
    private $recipient1;         // Recipient 1 for the transfer
    private $recipient2;         // Recipient 2 for the transfer

    /**
     * Init variables, first function called
     */

    protected function setUp(): void
    {
        echo "FileTest@ " . date("Y-m-d H:i:s") . "\n\n";

        $this->transferSubject = "Subject test";
        $this->transferMessage = "Message test";
        $this->srcFile = __DIR__ . DIRECTORY_SEPARATOR . 'file01.txt';
        $this->fileName = basename($this->srcFile);
        $this->fileSize = filesize($this->srcFile);
    }

    /**
     * Function to test creation of a file
     * 
     * @return int: $file->id if test succeed
     */
    public function testCreate() {
        // Creating transfer object
        $transfer = Transfer::create(date('Y-m-d',  strtotime("+5 days")));
        $transfer->subject = $this->transferSubject;
        $transfer->message = $this->transferMessage;
        $transfer->save();

        $file = File::create($transfer);
        $file->name = $this->fileName;
        $file->size = $this->fileSize;
        $file->mime_type='application/binary';

        $file->save();

        $this->assertNotNull($file->id);
        $this->assertTrue($file->id > 0);

        // uploading fake file
        $dest = rtrim(Config::get('storage_filesystem_path'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        StorageFilesystem::ensurePath( $dest, Storage::buildPath($file, false));
        copy($this->srcFile, Storage::buildPath($file) . "/" . $file->uid);
        $this->displayInfo(get_class($this), __FUNCTION__, ' -- File created:'.$file->id.' dest:'.$dest.' uuid:'.$file->uid.' subp:'.Storage::buildPath($file));

        return $file->id;
    }

    /**
     * Function to test file got from cache and database
     * 
     * @depends testCreate
     * @return int: $file->id if test succeed
     */
    public function testRead($fileId) {
        $this->assertTrue($fileId > 0);

        // Test from cache
        $file = File::fromId($fileId);

        $this->assertTrue($file->id == $fileId);
        $this->assertTrue($file->name == $this->fileName);
        $this->assertTrue($file->size == $this->fileSize);

        // Test from DB
        // Clean cache
        $cachePurged = DBObject::purgeCache(get_class($file), $fileId);
        $this->assertTrue($cachePurged);

        // Test from cache
        $file = File::fromId($fileId);

        $this->assertTrue($file->id == $fileId);
        $this->assertTrue($file->name == $this->fileName);
        $this->assertTrue($file->size == $this->fileSize);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- File got:' . $fileId);

        return $fileId;
    }

    /**
     * Function used to test updating file to database
     * 
     * @depends testRead
     * @return int: $file->id if test succeed
     */
    public function testUpdate($fileId) {
        $this->assertTrue($fileId > 0);

        $file = File::fromId($fileId);

        $newName = "fileNEW.txt";
        $newSize = "999";

        $file->name = $newName;
        $file->size = $newSize;

        $file->save();

        $this->assertTrue($file->id == $fileId);
        $this->assertTrue($file->name == $newName);
        $this->assertTrue($file->size == $newSize);

        $file->name = $this->fileName;
        $file->size = $this->fileSize;

        $file->save();

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- File updated:' . $fileId);

        return $fileId;
    }

    /**
     * Function used to storage
     * 
     * @depends testUpdate
     * @return int: $file->id if test succeed
     */    
    public function testStorage($fileId) {
        $this->assertTrue($fileId > 0);

        $file = File::fromId($fileId);

        $rawDatas = $file->readChunk();

        $this->assertNotNull($rawDatas);

        $this->displayInfo(get_class($this), __FUNCTION__, ' -- Row DATAS got from file:'.$fileId);

        return $fileId;
    }
 
    
    /**
     * Function used to test deletion of a file from database
     * 
     * @depends testStorage
     * @return boolean: true if test succeed
     */
    public function testDelete($fileId) {
        $this->assertTrue($fileId > 0);

        $file = File::fromId($fileId);

        $this->assertNotNull($file);

        $file->delete();

        // Clean cache
        $cachePurged = DBObject::purgeCache(get_class($file), $fileId);

        $isDeleted = false;
        try {
            $oldFile = File::fromId($fileId);
        } catch (FileNotFoundException $ex) {
            $isDeleted = true;
            $this->displayInfo(get_class($this), __FUNCTION__, ' -- File deleted:' . $fileId);
        }

        $this->assertTrue($isDeleted);

        return true;
    }

}
