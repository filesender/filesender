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
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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
 
if (!defined('FILESENDER_BASE')) die('Missing environment');

require_once FILESENDER_BASE.'classes/FileSystem.class.php';
require_once FILESENDER_BASE.'classes/File.class.php';      //Or should it be accessed from Transfer?

/**
 *  Represents an abstraction layer between the filedata in the database and
 *  the actual file storage implementation. Methods to process file chunks that are uploaded and 
 *  downloaded by the javascript in (primarily) js/multiupload.js and js/multidownload.js
 *  Static methods and properties only
 *  @property FileSystem $file
 *  @property array $filequeue: an array of DB file records w/ needed data
 *  @property string $uploadfolder: path to upload folder on server
 *  @property string $tempfolder:   path to temp folder on server
 *  @property int $chunksize
 *  @property string $storage_type: type of storage system (default is 'filesystem')
 *  @property boolean $calc_hash_chunks: sets whether to calculate and compare hashes per chunk
 */
 
 /**  Description of how file upload works NOW:
  *   index.php?s=upload, requires => uploadcheck.php, requires => multiupload.php
  *   index.php?s=upload executes multiupload.js
  *   multiupload.js requests fs_multi_upload.php that processes uploaded chunks on server
  *   
  */
class FileStorage
{
    //File related config directives: site_filestore = upload dir, site_temp_filestore, upload_chunk_size
    //Some new ones: site_storage_type (i.e. filesystem, various remote filestoring systems)

    //Properties
    private static $file = null;
    private static $filequeue = null;        //an array of files to be uploaded
    private static $uploadfolder = null;
    private static $tempfolder = null;
    private static $chunksize = null;
    private static $storage_type = null;
    private static $calculate_hash_chunks = false;
    

    ////////////
    // Methods
    //
    ////////////
    
    /**
     *  Gets the configs and sets other needed properties
     */
    private static function load()
    {   
        if (!self::$uploadfolder)
            self::$uploadfolder = Config::get('site_filestore');
        if (!self::$tempfolder)
            self::$tempfolder = Config::get('site_temp_filestore');
        if (!self::$chucksize)
            self::$chucksize = Config::get('upload_chunk_size');
        if (!self::$storage_type)
            self::$storage_type = Config::get('site_storage_type');

    }

    
    /**
     *  Sets the queue property to an array of files
     *  To be used by a Transfer instance or interface script?
     *  @param $files (what type? Just the names as strings?)
     *  @throws FileQueueException
     */
    public static function setQueue($files)
    {
        //Making sure that $files is not empty
        if (is_null($files))
            throw new FileQueueException('Queue empty');    //just an example of usage of this exc type
        //Make sure that files queue is not changed from being an array
        elseif (count($files) == 1)
            self::$filequeue = array($files);
        //Otherwise, simply assign $files to queue property
        else
            self::$filequeue = $files;
        
    }
    
    
    /**
     *  Adds the files in the argument to an existing queue
     *  Sets the queue if it's empty
     *  @param $files (array of File objects or their file names - strings)
     */
    public static function addToQueue($files)
    {
        if (count(self::$filequeue) == 0) {
            setQueue($files);
        }
        
        //Making sure that $files is not empty
        if (is_null($files))
            throw new FileQueueException('Queue empty');    //just an example of usage of this exc type
        //Make sure that files queue is not changed from being an array
        elseif (count($files) == 1)
            self::$filequeue = array($files);
        //Otherwise, simply assign $files to queue property
        else
            self::$filequeue = $files;
        
        //removes duplicate files from queue  - very naive way: assumes the array contains strings
        self::$filequeue = array_unique(self::$filequeue);
        // A more robust way would be to do 
        //for ($i = 0; $i < count($files); $i++) {
        //  foreach ($files as $file) { $file->getFilename() == $files[$i]->getFilename(); remove $file; }}
    }
    
    
    /**
     *  Generates temp filename using filename / voucher id
     *
     */
    public static function generateTempFilename()
    {
        
    }
    
    
    /** 
     *  Renames a temporary file
     *
     */
    public static function renameTempFile()
    {
    
    }
    
    /**
     *  Initiates upload of a file
     *  @param string $filename
     *
     */
    public static function uploadfile()
    {
    
    }
    
    /**
     *  Uploads a file(s)
     *  Takes the chunks in the temp storage folder
     *  Calculates their hash (sha1) (a static method in Utilities?),
     *  compares the one calculated before upload (possible?) with the uploaded chunk's
     *
     *  TODO: implement/restructure the server processing of uploaded chunks in fs_multi_upload.php  
     *  
     */
    public static function processUploadedFile()
    {
        
    }
    
    /**
     *  Downloads a file
     *  TODO: Need to check how the download works in pre-alpha 2.0
     *
     */
    public static function download()
    {
        
    }
}
