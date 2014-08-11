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
 *  Gives access to a file on the filesystem 
 */
class StorageFilesystem {
    /**
     * Storage path
     */
    private static $path = null;
    
    /**
     * Folder hashing
     */
    private static $hashing = null;
    
    /**
     * Storage setup, loads options from config
     */
    private static function setup() {
        if(!is_null(self::$path)) return;
        
        $path = Config::get('storage_filesystem_path');
        if(!$path) throw new ConfigMissingParameterException('storage_filesystem_path');
        
        if(!is_dir($path) || !is_writable($path))
            throw new StorageFilesystemCannotWriteException($path);
        
        self::$path = $path;
        
        $hashing = Config::get('storage_filesystem_hashing');
        if($hashing) self::$hashing = $hashing;
    }
    
    /**
     * Checks for free space on the disk where file will be stored
     * 
     * @param File $file
     *
     * @return float $availablembytes: free space in mbytes
     */
    public static function getFreeSpace(File $file) {
        $location = self::buildPath($file);
        
        $free = disk_free_space($location);
        
        return $free;
    }
    
    /**
     * Build file storage path (without file uid)
     * 
     * @param File $file
     * 
     * @return string path
     */
    private static function buildPath(File $file) {
        self::setup();
        
        $path = self::$path;
        if(substr($path, -1) != '/') $path .= '/';
        
        // Is storage path hashing enabled
        if(self::$hashing) {
            $subpath = '';
            
            if(is_numeric(self::$hashing)) {
                // Prepend self::$hashing letters from $file->uid as subfolders of $path
                for($i=0; $i<self::$hashing; $i++)
                    $subpath .= substr($file->uid, $i, 1).'/';
                
            }else if(is_callable(self::$hashing)) {
                // Call self::$hashing with $file to get sub-path
                $subpath = trim(trim(self::$hashing($file)), '/');
            }
            
            if($subpath) { // Ensure that subpath exists and is writable
                $p = $path;
                foreach(array_filter(explode('/', $subpath)) as $sub) {
                    $p .= $sub;
                    
                    if(!is_dir($p) && !mkdir($p))
                        throw new StorageFilesystemCannotCreatePathException($p);
                    
                    if(!is_writable($p))
                        throw new StorageFilesystemCannotWriteException($p);
                    
                    $p .= '/';
                }
            }
        }
        
        return $path;
    }
    
    /**
     *  Reads chunk at offset
     *
     * @param File $file
     * @param uint $offset offset in bytes
     * 
     * @return mixed chunk data encoded as string or null if no chunk remaining
     * 
     * @throws StorageFilesystemFileNotFoundException
     * @throws StorageFilesystemCannotReadException
     */
    public function readChunk(File $file, $offset = 0) {
        $chunk_size = (int)Config::get('download_chunk_size');
        
        $file_path = self::buildPath($file).$file->uid;
        
        if(!file_exists($file_path))
            throw new StorageFilesystemFileNotFoundException($file_path);
        
        // Open file for reading
        if($fh = fopen($file_path, 'rb')) {
            // Sets position of file pointer
            if($offset) fseek($fh, $offset);
            
            // Try to read chunk
            $chunk_data = fread($fh, $chunk_size);
            
            // Close reader
            fclose($fh);
            
            if($chunk_data === false) return null; // No data remaining
            
            return $chunk_data;
            
        }else throw new StorageFilesystemCannotReadException($file_path);
    }
    
    /**
     * Write a chunk of data to file at offset
     * 
     * @param File $file
     * @param string $data the chunk data
     * @param uint $offset offset in bytes
     * 
     * @return array with offset and written amount of bytes
     * 
     * @throws StorageFilesystemOutOfSpaceException
     * @throws StorageFilesystemCannotWriteException
     */
    public function writeChunk(File $file, $data, $offset = null) {
        $chunk_size = strlen($data);
        
        $file_path = self::buildPath($file).$file->uid;
        
        $free_space = self::getFreeSpace($file);
        if($free_space <= $chunk_size) {
            throw new OutOfSpaceException($path);
        }
        
        // Open file for writing
        $mode = file_exists($file_path) ? 'rb+' : 'wb+'; // Create file if it does not exist
        if($fh = fopen($file_path, $mode)) {
            // Sets position of file pointer
            if($offset) {
                fseek($fh, $offset); // Known offset
            }else if(is_null($offset)) {
                fseek($fh, 0, SEEK_END); // End of file if no offset given
            }
            
            // Get offset
            $offset = ftell($fh);
            
            // Try to write chunk
            $written = fwrite($fh, $data);
            
            // Close writer
            fclose($fh);
            
            return array(
                'offset' => $offset,
                'written' => $written
            );
        }else throw new StorageFilesystemCannotWriteException($file_path);
    }
    
    /**
     * Deletes a file
     * 
     * @param File $file
     * 
     * @throws StorageFilesystemCannotDeleteException
     */
    public function deleteFile(File $file) {
        $file_path = self::buildPath($file).$file->uid;
        
        if(!file_exists($file_path)) return;
        
        if(!unlink($file_path))
            throw new StorageFilesystemCannotDeleteException($file_path);
    }
    
    /**
     * Tells wether storage support file digests
     * 
     * @return bool
     */
    public static function supportsDigest() {
        return true;
    }
    
    /**
     * Computes the digest of a file
     * 
     * @param File $file
     * 
     * @return string hex digest
     */
    public static function getDigest(File $file) {
        $file_path = self::buildPath($file).$file->uid;
        
        return sha1_file($file_path);
    }
}

