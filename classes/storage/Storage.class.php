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


/**
 *  Represents an abstraction layer between the filedata in the database and
 *  the actual file storage implementation.  
 *
 *  @property StorageFileSystem $file
 *  @property array $filequeue: an array of DB file records w/ needed data
 */
class Storage
{
    private static $file = null;
    //private static $filequeue = null;        //an array of files to be uploaded
    

    /**
     *  Gets the configs and sets other needed properties
     *
     *  @throws ConfigParamNotSet with (string) config key, (string) error level
     *  @return false if a config directive is not set in config.php
     *  @return true when everything is set properly and function exits successfully
     */
    private static function load()
    {   
        if (is_null(self::$file)) {

            //Default upload folder should exist in config. If not:
            if (is_null(Config::get('filestorage_filesystem_file_location'))) {
                throw new ConfigParamNotSet(
                    'filestorage_filesystem_file_location',
                    'fatal'
                );

                return false;
            }

            //Default temp folder should exist in config. If not:
            if (is_null(Config::get('filestorage_filesystem_temp_location'))) {
                throw new ConfigParamNotSet(
                    'filestorage_filesystem_temp_location', 
                    'fatal'
                );

                return false;
            }

            // If no default chunk size in config:
            if (is_null(Config::get('upload_chunk_size'))) {
                throw new ConfigParamNotSet('upload_chunk_size', 'warn');

                return false;
            }

            $storage = Config::get('filestorage_type');

            switch ($storage) {
                case 'filesystem': self::$file = new StorageFileSystem(); break;
                // ... continue adding storage types here
            
                //Defaults to local file system, with all chunks on same disk
                case null:  // no break; here
                default: self::$file = new StorageFileSystem(); break;
            }

            return true;
        }
    }
    
    
    /**
     *  Writes a chunk at offset
     *
     *  @param File $dbfile: a File object
     *  @param mixed $chunk: chunk data or null (if $data == null)
     *  @param int $offset: the chunk offset in the file
     *  @return boolean true (if written successfully); false otherwise
     */
    public static function writeChunk(File $dbfile, $chunk, $offset = null)
    {
        //loads prelims
        if (self::load())
            return self::$file::writeChunk($dbfile, $chunk, $offset);

    }


    /**
     *  Reads a chunk at offset
     *
     *  @param File $dbfile: a File object with info about the file
     *  @param int $offset: the chunk offset in the file
     *  @return mixed $chunk: chunk data or null if no more data available
     */
    public static function readChunk(File $dbfile, $offset = null)
    {
        if (self::load())
            return self::$file::readChunk($dbfile, $offset);
    }

    /**
     *  Deletes a file from storage
     *
     *  @param File $dbfile: a File object with info about file
     *  @return true on success, otherwise false
     */
    public static function delete(File $dbfile)
    {
        if (self:load())
            return self::$file::delete($dbfile);
    }

    /**
     *  Calculates hash (sha1 algorithm) of given file and returns it
     *
     *  @param File $dbfile: The File object with file info
     *  @return string of characters
     */
    public static function getHash(File $dbfile)
    {
        if (self::load())
            return self::$file::getHash($dbfile);
    }

    /** NOT REALLY NEEDED HERE

     *  Sets the queue property to an array of files
     *  To be used by a Transfer instance or interface script?
     *  @param $files (what type? Just the names as strings?)
     *  @throws FileQueueException
     */
    /**public static function setQueue($files)
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
        
    }*/
    
    
    /** NOT REALLY NEEDED HERE

     *  Adds the files in the argument to an existing queue
     *  Sets the queue if it's empty
     *  @param $files (array of File objects or their file names - strings)
     */
    /*public static function addToQueue($files)
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
    }*/
}
