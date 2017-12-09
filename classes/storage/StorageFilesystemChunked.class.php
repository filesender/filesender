<?php
/*
 * Store the file as chunks instead of as a single file on disk.
 *
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
 *
 *  This class stores the chunks that are sent as individual files 
 *
 *  One use case for this is to use FUSE to efficiently hand the data off to EOS
 *  https://twiki.cern.ch/twiki/bin/view/EOS/WebHome 
 */
class StorageFilesystemChunked extends StorageFilesystem {
    
    
    public static function getOffsetWithinChunkedFile($file_path,$offset) {
        $file_chunk_size = Config::get('upload_chunk_size');
        return ($offset % $file_chunk_size);
    }
    
    public static function getChunkFilename($file_path,$offset) {
        $file_chunk_size = Config::get('upload_chunk_size');
        $offset = $offset - ($offset % $file_chunk_size);
	return $file_path.'/'.str_pad($offset,24,'0',STR_PAD_LEFT);
    }

    /**
     *  Reads chunk at offset
     *
     * @param File $file
     * @param uint $offset offset in bytes
     * @param uint $length length in bytes
     * 
     * @return mixed chunk data encoded as string or null if no chunk remaining
     * 
     * @throws StorageFilesystemFileNotFoundException
     * @throws StorageFilesystemCannotReadException
     */
    public static function readChunk(File $file, $offset, $length) {

	if ($file->transfer->options['encryption'])
	    $offset=$offset/Config::get('upload_chunk_size')*Config::get('upload_crypted_chunk_size');

        $file_path = self::buildPath($file).$file->uid;

	$chunkFile=self::getChunkFilename($file_path,$offset);

        if(!file_exists($chunkFile))
            throw new StorageFilesystemFileNotFoundException($file_path, $file);

	$data = file_get_contents($chunkFile);
	if ($data === FALSE) return null;

	return $data;
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
    public static function writeChunk(File $file, $data, $offset = null) {

        $chunk_size = strlen($data);
        
        $path = self::buildPath($file);
        
        $free_space = disk_free_space($path);
        //Logger::info('MD Chunked: writeChunk free_space:'.$free_space);
        if($free_space <= $chunk_size)
            throw new StorageNotEnoughSpaceLeftException($chunk_size);
        
        $file_path = $path.$file->uid;
        

	if (!file_exists($file_path)) {
            //Logger::info('MD Chunked: Chunk folder missing for file. Creating');
	    mkdir($file_path, 0770, true);
	}
	$chunkFile=self::getChunkFilename($file_path,$offset);
        $i=0;
        $fh = fopen($chunkFile, 'wb');
        while ($fh === false && $i<100) {
            sleep(1);
            $fh = fopen($chunkFile, 'wb');
            $i++;
        }
        if($fh !== false) {

            $written = fwrite($fh, $data, $chunk_size);
            fclose($fh);

            if( $chunk_size != $written ) {
                Logger::info('writeChunk() Can not write to : '.$chunkFile);
                throw new StorageFilesystemCannotWriteException('writeChunk( '.$file_path, $file, $data, $offset, $written );
            }

            return array(
                'offset' => $offset,
                'written' => $written
            );
        } else {

            Logger::info('MD Chunked: writeChunk() Can not write to : '.$chunkFile);
            throw new StorageFilesystemCannotWriteException('writeChunk( '.$file_path, $file);
        }
    }
    
    /**
     * Handles file completion checks
     * 
     * @param File $file
     */
    public static function completeFile(File $file) {
        self::setup();
        $file_path = self::buildPath($file).$file->uid;
        
    }
    
    /**
     * Deletes a file
     * 
     * @param File $file
     * 
     * @throws StorageFilesystemCannotDeleteException
     */
    public static function deleteFile(File $file) {
        $file_path = self::buildPath($file).$file->uid;
        
        if(!file_exists($file_path)) return;
        
        if(is_link($file_path)) {
            if(!unlink($file_path))
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            return;
        }
        
        $rm_command = Config::get('storage_filesystem_tree_deletion_command');
        $cmd = str_replace('{path}', escapeshellarg($file_path), $rm_command);
        exec($cmd, $out, $ret);
        
        if($ret)
            throw new StorageFilesystemCannotDeleteException($file_path, $file);
        
    }
    
    
    
    
    /**
     * Store a whole file
     * 
     * @param File $file
     * @param string $source_path path to file data
     * 
     * @return bool
     * 
     * @throws StorageFilesystemOutOfSpaceException
     */
    public static function storeWholeFile(File $file, $source_path) {
	return self::writeChunk($file, file_get_contents($source_path), 0);
    }

    public static function getStream(File $file) {
        StorageFilesystemChunkedStream::ensureRegistered();
        $path = "StorageFilesystemChunkedStream://" . $file->uid;
        $fp = fopen( $path, "r+");
        return $fp;
    }

}
