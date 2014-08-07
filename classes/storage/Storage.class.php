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
 *  Represents an abstraction layer to access file data in configured storage 
 */
class Storage {
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
    private static function setup() {   
        if(!is_null(self::$class)) return;
        
        if(!Config::get('upload_chunk_size'))
            throw new ConfigMissingParameterException('upload_chunk_size');
        
        if(!Config::get('download_chunk_size'))
            throw new ConfigMissingParameterException('download_chunk_size');
        
        $type = Config::get('storage_type');
        if(!$type)
            throw new ConfigMissingParameterException('storage_type');
        
        $class = 'Storage'.ucfirst($type);
        
        if(!class_exists($class))
            throw ConfigBadParameterException('storage_type');
        
        self::$class = $class;
    }
    
    /**
     * Delegates free space check
     * 
     * @param File $file
     * 
     * @return mixed int (bytes) or null if it doesn't make sense (virtually infinite space like cloud storage ?)
     */
    public static function getFreeSpace(File $file) {
        self::setup();
        
        call_user_func(self::$class.'::getFreeSpace', $file);
    }
    
    /**
     *  Delegates chunk read
     * 
     * @param File $file
     * @param uint $offset offset in bytes
     * 
     * @return mixed chunk data encoded as string or null if no chunk remaining
     */
    public static function readChunk(File $file, $offset = null) {
        self::setup();
        
        if(is_null($offset)) { // Stream reading next chunk
            if(array_key_exists($file->id, self::$reading_offsets)) { // Did we already start to read this file ?
                $offset = self::$reading_offsets[$file->id];
            }else $offset = 0;
        }
        
        $data = call_user_func(self::$class.'::readChunk', $file, $offset);
        
        self::$reading_offsets[$file->id] = $offset + (int)Config::get('download_chunk_size');
        
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
    public static function writeChunk(File $file, $data, $offset = null) {
        self::setup();
        
        if(strlen($data) > (int)Config::get('upload_chunk_size')) // We should not get more than upload_chunk_size bytes of data
            throw new StorageChunkTooLargeException(strlen($data), (int)Config::get('upload_chunk_size'));
        
        return call_user_func(self::$class.'::writeChunk', $file, $data, $offset);
    }
    
    /**
     * Delegates file deletion
     *
     * @param File $file
     */
    public static function delete(File $file) {
        self::setup();
        
        call_user_func(self::$class.'::delete', $file);
    }
    
    /**
     * Delegates digest support check
     * 
     * @return bool
     */
    public static function supportsDigest() {
        self::setup();
        
        call_user_func(self::$class.'::supportsDigest');
    }
    
    /**
     * Delegates digest computation
     * 
     * @param File $file
     * 
     * @return string hex digest
     */
    public static function getDigest(File $file) {
        self::setup();
        
        call_user_func(self::$class.'::getDigest', $file);
    }
}
