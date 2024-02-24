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
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}


/**
 *  Represents an abstraction layer to access file data in configured storage
 */
class Storage
{
    /**
     * Cache if delegation class was loaded.
     */
    private static $class = null;
    
    /**
     * Stream reading offsets
     */
    private static $reading_offsets = array();
    
    /**
     *  Gets the configs and sets other needed properties
     *
     *  @throws ConfigParamNotSet with (string) config key, (string) error level
     *  @return false if a config directive is not set in config.php
     *  @return true when everything is set properly and function exits successfully
     */
    private static function setup()
    {
        if (!is_null(self::$class)) {
            return;
        }
        
        // Check required config parameters
        if (!Config::get('upload_chunk_size')) {
            throw new ConfigMissingParameterException('upload_chunk_size');
        }
        
        if (!Config::get('download_chunk_size')) {
            throw new ConfigMissingParameterException('download_chunk_size');
        }
        
        $type = Config::get('storage_type');
        if (!$type) {
            throw new ConfigMissingParameterException('storage_type');
        }
        
        // Build storage underlying class name and check if it exists
        $class = 'Storage'.ucfirst($type);
        
        if (!class_exists($class)) {
            throw new ConfigBadParameterException('storage_type');
        }
        
        // Cache name
        self::$class = $class;
    }

    /**
     * Get the storage class that was used with this file
     *
     * @param File $file
     *
     * @return String
     */
    public static function getStorageClass(File $file)
    {
        return $file->storage_class_name;
    }

    /**
     * Get the storage class to use for new files
     *
     * @return String
     */
    public static function getDefaultStorageClass()
    {
        self::setup();
        return self::$class;
    }
    
    
    /**
     * Delegates transfer storable check
     *
     * @param Transfer $transfer
     *
     * @return bool
     */
    public static function canStore(Transfer $transfer)
    {
        self::setup();
        
        return call_user_func(self::getDefaultStorageClass().'::canStore', $transfer);
    }
    
    /**
     * Delegates space usage check
     *
     * @return array of usage data for individual sub-storages
     */
    public static function getUsage()
    {
        self::setup();
        
        if (!method_exists(self::getDefaultStorageClass(), 'getUsage')) {
            return null;
        }
        
        return call_user_func(self::getDefaultStorageClass().'::getUsage');
    }
    
    /**
     *  Delegates chunk read
     *
     * @param File $file
     * @param uint $offset offset in bytes
     * @param uint $length length in bytes
     *
     * @return mixed chunk data encoded as string or null if no chunk remaining
     */
    public static function readChunk(File $file, $offset = null, $length = null)
    {
        self::setup();
        
        // If no length provided use download_chunk_size config parameter value
        $length = (int)$length;
        if (!$length) {
            $length = (int)Config::get('download_chunk_size');
            if (!$length) {
                $length = 1024 * 1024;
            }
        }
        
        // If no offset provided check if we already started to read this file
        if (is_null($offset)) { // Stream reading next chunk
            if (array_key_exists($file->id, self::$reading_offsets)) {
                $offset = self::$reading_offsets[$file->id];
            } else {
                $offset = 0;
            }
        }
        
        // Ask underlying class to read data
        $data = call_user_func(self::getStorageClass($file).'::readChunk', $file, $offset, $length);
        
        // Update read offset
        self::$reading_offsets[$file->id] = $offset + $length;
        
        return $data;
    }
    
    /**
     * Delegates chunk write
     *
     * @param File $file
     * @param string $data the chunk data
     * @param uint $offset offset in bytes
     *
     * @return array with offset and written amount of bytes
     *
     * @throws StorageChunkTooLongException
     */
    public static function writeChunk(File $file, $data, $offset = null)
    {
        self::setup();
        
        // Forbid to write chunks whose size is over upload_chunk_size config parameter's value
        if (strlen($data) > (int)Config::get('upload_crypted_chunk_size')) {
            throw new StorageChunkTooLargeException(strlen($data), (int)Config::get('upload_chunk_size'));
        }
        
        $bench = new Benchmark('writeChunk', 'benchmark_writeChunk');
        $bench->start();
        
        // Ask underlying class to write data
        $ret = call_user_func(self::getStorageClass($file).'::writeChunk', $file, $data, $offset);
        
        $bench->log();
        return $ret;
    }
    
    /**
     * Delegates file completion (delegation classes can implement it optionaly)
     *
     * @param File $file
     */
    public static function completeFile(File $file)
    {
        self::setup();
        
        if (!method_exists(self::getStorageClass($file), 'completeFile')) {
            return;
        }
        
        return call_user_func(self::getStorageClass($file).'::completeFile', $file);
    }
    
    /**
     * Delegates file deletion
     *
     * @param File $file
     */
    public static function deleteFile(File $file)
    {
        self::setup();
        
        call_user_func(self::getStorageClass($file).'::deleteFile', $file);
    }
    
    /**
     * Delegates digest support check
     *
     * @return bool
     */
    public static function supportsDigest()
    {
        self::setup();
        
        call_user_func(self::getDefaultStorageClass().'::supportsDigest');
    }
    
    /**
     * Delegates digest computation
     *
     * @param File $file
     *
     * @return string hex digest
     */
    public static function getDigest(File $file)
    {
        self::setup();
        
        call_user_func(self::getStorageClass($file).'::getDigest', $file);
    }
    
    /**
     * Delegates whole file support check
     *
     * @return bool
     */
    public static function supportsWholeFile()
    {
        self::setup();
        
        return call_user_func(self::getDefaultStorageClass().'::supportsWholeFile');
    }
    
    /**
     * Delegates whole file supporing
     *
     * @param File $file
     * @param string $source_path path to file data
     *
     * @return bool
     *
     * @throws StorageFilesystemOutOfSpaceException
     */
    public static function storeWholeFile(File $file, $source_path)
    {
        self::setup();
        
        call_user_func(self::getStorageClass($file).'::storeWholeFile', $file, $source_path);
    }
    
    /**
     * Delegates linking support check
     *
     * @return bool
     */
    public static function supportsLinking()
    {
        self::setup();
        
        return call_user_func(self::getDefaultStorageClass().'::supportsLinking');
    }
    
    /**
     * Delegates file linking
     *
     * @param File $file
     * @param string $source_path path to file data
     */
    public static function storeAsLink(File $file, $source_path)
    {
        self::setup();
        
        call_user_func(self::getStorageClass($file).'::storeAsLink', $file, $source_path);
    }



    /**
     * get a file as a stream. The stream only needs to support reading all bytes
     * in order, start to finish.
     *
     * @param File $file
     */
    public static function getStream(File $file)
    {
        self::setup();
        return call_user_func(self::getStorageClass($file).'::getStream', $file);
    }


    public static function buildPath(File $file, $fullPath = true )
    {
        self::setup();
        return call_user_func(self::getStorageClass($file).'::buildPath', $file, $fullPath );
    }
}
