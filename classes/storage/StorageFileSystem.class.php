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
 *  @property string $tempdir: path to local temporary directory
 *  @property string $uploaddir: path to local upload dir
 *  @property FileSystem $instance: holds a single instance of this class
 *  @property uint $chunksize
 *  @property bool $calc_hash:    whether calculating hash for storage type is supported
 */
class StorageFileSystem
{
    ////////////
    //Properties
    ////////////
    private $uploadfolder = null;
    private $tempfolder = null;
    private $chunksize  = null;
    private static $instance = null;
    private $calculate_hash = false;


    /**
     *  Gets the FileSystem object
     *  Creates a new one, if none exists (need only one, hence singleton class declaration)
     */
    public static function getInstance()
    {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }


    /**
     *  Constructor: creates a new instance and sets
     *  properties from config
     */
    private function __construct()
    {
        $this->tempfolder = Config::get('filestorage_filesystem_file_location');
        $this->uploadfolder = Config::get('filestorage_filesystem_temp_location');

        // If chunk size not defined in config
        if (is_null(Config::get('upload_chunk_size')))
            $this->chunksize = 2*1024*1024;    // defaults to 2 mbytes
        else
            $this->chunksize = Config::get('upload_chunk_size');

        $this->calculate_hash = Config::get('filestorage_filesystem_calc_hash');
    }


    /**
     *  Default getter - instance getter could also be added here
     *  @param string $pname: property name
     *  @returns property with name = $pname
     */
    public function __get($pname)
    {
        if(in_array($pname, array('uploadfolder', 'tempfolder', 'chunksize',
            'calculate_hash' )))
            return $this->pname;
        //If property $pname does not exist
        throw new PropertyAccessException($this, $pname);
        
    }
    

    /**
     *  Default setter: sets property $pname to value $value
     *  @param string @pname: property name
     *  @param string @value: value to set to
     */
    public function __set($pname, $value)
    {
        if(in_array($pname, array('chunksize')) && !is_null($value))
            $this->pname = $value;
        throw new PropertyAccessException($this, $pname);
    }


    /**
     *  Reads chunk at offset
     *
     *  @param File $dbfile: The database file object with file info
     *  @param uint $offset: offset as no. of bytes
     *  @throws StorageAccessException if file cannot be read
     *  @throws FileNotFoundException if file cannot be found at location
     *  @throws NoDataAtOffsetException
     *  @return string chunk, chunk data enc as string, or false
     */
    public function readChunk(File $dbfile, $offset)
    {
        /* setting execution time limit, sending headers, etc. is delegated to
         * the webservice (in rest/)
         * set_time_limit(0); */

        if ($offset)
            $chunksize = $offset; //in bytes
        else
            $chunksize = 1 * 1024 * 1024;  // in bytes

        $chunk = '';
        
        $path = $this->uploadfolder.$dbfile->name;

        //Locates file, opens it for reading, reads, returns data
        try {
            if (!file_exists($path))
                throw new StorageFileSystemNotFoundException($path);

            if (($file = fopen($this->uploadfolder.$dbfile->name, 'rb')) != true)
                throw new StorageAccessException(get_class());

            // Sets position of file pointer
            if ( $offset != 0 ) {
                fseek($file, $offset);
            }

            if (($chunk = fread($file, $chunksize)) == false)
                // This might not be needed
                throw new NoDataAtOffsetException();

            fclose($file);
            return $chunk;

        } catch (StorageAccessException $e) {
        
        } catch (StorageFileSystemNotFoundException $e) {

        } catch (NoDataAtOffsetException $e) {

        }
        
        //failed opening file
        return false;
    }


    /**
     *  Write a chunk of data to file (appends if not empty)
     *
     *  @param File $dbfile: object with file info
     *  @param string $chunk: the chunk of data to write
     *  @param uint $offset: offset as no. of bytes
     *  @throws StorageAccessException if file cannot be written to
     *  @throws OutOfSpaceException if disk doesn't have space for chunk
     *  @throws CannotWriteToFileException if any other write error
     *  @return true if written, false if error
     */
    public function writeChunk(File $dbfile, $chunk, $offset)
    {
        // if file doesn't exist: creates it
        $path = $this->uploadfolder.$dbfile->name;

        try {
            if (($file = fopen($path, 'w')) != true)
                throw new StorageAccessException(get_class());    //failed opening file
            
            // set file position pointer to $offset
            // if postdata was accessed using php://input, fseek cannot be used,
            // if using php://stdin - yes
            // should use: $fdata = fopen('php://input', 'r'); while(
            // fread($fdata, $chunk) { writeChunk() } in calling method
            if ($offset)
                fseek($file, $offset);

            if ($this->getFreeSpace() <= strlen($chunk)) {
                throw new OutOfSpaceException($path);
            }

            $written = fwrite($file, $chunk); //$written = nr of bytes

            fclose($file);

            if ($written)
                return $written;
            else
                throw new CannotWriteToFileException($path);

        } catch (StorageAccessException $eaccess) {

        } catch (OutOfSpaceException $espace) {
        
        } catch (CannotWriteToFileException $ewrite) {

        }

        //failed to write to file
        return false;
    }


    /**
     *  Deletes the file given as argument
     *
     *  @param File $dbfile:    File object
     *  @throws StorageFileSystemNotFoundException
     *  @return true if successful, false oterwise
     */
    public function delete(File $dbfile)
    {
        $path = $this->uploadfolder.$dbfile->name;
        try {
            if(!file_exists($path))
                throw new StorageFileSystemNotFoundException();
            return unlink($path);
        } catch (StorageFileSystemNotFoundException $e) {

        }

        return false;
    }


    /**
     *  Checks for unused space on the disk
     *  where upload dir is located
     *
     *  @return float $availablembytes: free space in mbytes
     */
    public function getFreeSpace()
    {
        $udir = $this->uploadfolder;
        $availablebytes = disk_free_space($udir);

        $availablembytes = $availablebytes / 1024 / 1024;

        return $availablembytes;
    }


    /**
     *  Get hash
     *
     *  @param File $dbfile with file info
     *  @return string hash code; false if hash calculation not supported or error
     */
    public function getHash(File $dbfile)
    {
        if (!$this->calculate_hash)
            return false;
        
        $sha1 = sha1_file($this->uploadfolder.$dbfile->name);

        return $sha1;
    }
}

