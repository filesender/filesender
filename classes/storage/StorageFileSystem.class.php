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
 *  Represents a file on the filesystem 
 *
 *  @property string $localpath:    path to file on local file system
 *  @property string $filename:     name of file on local file system
 *  @property FileSystem $instance: holds a single instance of this class
 */
class StorageFileSystem
{
    ////////////
    //Properties
    ////////////
    private $uploadfolder = null;
    private $tempfolder = null;
    private static $chunksize  = null;
    private static $instance = null;
    private static $calc_hash_chunks = false;
    

    /**
     *  Gets the FileSystem object
     *  Creates a new one, if none exists (need only one, hence singleton class declaration)
     *  @param string $name = null
     *  @param string $path = null
     *  @throws MissingFileParamsException
     */
    public static function getInstance($name = null, $path = null)
    {
        if(is_null($instance) && $name != null && $path == null)
            throw new MissingFileParamsException('Missing path');
        elseif(is_null($instance) && $name == null && $path != null)
            throw new MissingFileParamsException('Missing filename');
        elseif(is_null($instance) && $name != null && $path != null)
            self::$instance = new self($name, $path);
        else
            throw new MissingFileParamsException('Missing parameters');
        return self::$instance;
    }
    
    /**
     *  Constructor: creates a new instance with the parameters
     *  @param string $name: original filename
     *  @param string $path: local filesystem path to file
     */
    private function __construct($name, $path)
    {
        $this->uploadfolder = Config::get('site_filestore');
        $this->tempfolder = Config::get('site_temp_filestore');

        // If chunk size not defined in config
        if (is_null(Config::get('upload_chunk_size'))) {
            $this->chunksize = 20*1024*1024;    // defaults to 20 mbytes
        else    
            $this->chunksize = Config::get('upload_chunk_size');


    }
    
    /**
     *  Default getter
     *  @param string $pname: property name
     *  @returns property with name = $pname
     */
    public static __get($pname)
    {
        if(in_array($pname, 'localpath', 'filename'))
            return self::$pname;
        //If property $pname does not exist
        throw new PropertyAccessException();
        
    }
    
    /**
     *  Default setter: sets property $pname to value $value
     *  @param string @pname: property name
     *  @param string @value: value to set to
     */
    public static __set($pname, $value)
    {
        if(in_array($pname, 'localpath', 'filename') && !is_null($value))
            self::$pname = $value;
        throw new PropertyAccessException();
    }

    /**
     *  Reads chunk at offset
     *
     *  @param File $dbfile: The database file object with file info
     *  @param uint $offset: offset as no. of bytes
     *  @throws FileAccessException if file cannot be read
     *  @throws FileNotFoundException if file cannot be found at location
     *  @throws NoDataAtOffsetException
     *  @return mixed chunk: chunk data
     */
    public static readChunk($dbfile, $offset)
    {
        //Locates file and opens it for reading - FileAccessException if cannot open
        
        //reads chunk of size Config::get('chunk_size'), closes file  and
        //returns data
        
        return $chunk;
    }

    /**
     *  Write a chunk of data to file (appends if not empty)
     *
     *  @param File $dbfile: object with file info
     *  @param mixed $chunk: the chunk of binary data to write
     *  @param uint $offset: offset as no. of bytes
     *  @throws FileAccessException if file cannot be written to
     *  @throws OutOfSpaceException if disk doesn't have space for chunk
     */
    public static storeChunk($dbfile, $chunk, $offset)
    {
        // if file doesn't exist: creates it
        // opens file for writing
        // writes data to file
        // closes file

        // Create file in tmp
        try {
            $file = fopen(self::$tempfolder.$dbfile->name, 'w');    //sets up a
            //handle to file
            $written = fwrite($file, $chunk);
        } catch (FileAccessException $faexp) {
        } catch (OutOfSpaceException $oosexp) {
        }

    }
}
