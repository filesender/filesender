<?php
/*
 * Store the file using external script
 *
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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
 *  Gives access to a file on the filesystem
 *
 *  This class stores the file using external script
 *
 *  One use case for this is to use python xroot to efficiently hand the data off to EOS
 *  https://twiki.cern.ch/twiki/bin/view/EOS/WebHome
 */
class StorageFilesystemExternal extends StorageFilesystem
{
    public static function run($cmdOptions='', $data='')
    {
        $cmd=Config::get('storage_filesystem_external_script');
        if ($cmdOptions!='') {
            $cmd.=' '.$cmdOptions;
        }

        $output=array();
        $descriptorspec = array(
       0 => array("pipe", "r"),
       1 => array("pipe", "w"),
       2 => array("pipe", "w")
    );
        $process = proc_open(
        $cmd,
            $descriptorspec,
            $pipes
    );

        if ($data!='') {
            fwrite($pipes[0], $data);
        }
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_status = proc_close($process);

        if ($exit_status!=0) {
            Logger::info('StorageFilesystemExternal: stdout'.str_replace(array("\r\n","\n"), "\n", $stdout));
            Logger::info('StorageFilesystemExternal: stderror'.str_replace(array("\r\n","\n"), "\n", $stderr));
        }
        if ($stderr!='') {
            Logger::info('StorageFilesystemExternal: stderror'.str_replace(array("\r\n","\n"), "\n", $stderr));
        }

        return array(
        'stdout' => $stdout,
        'stderror' => $stderr,
        'status' => $exit_status
    );
    }

    public static function canStore(Transfer $transfer)
    {
        return true;
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
    public static function readChunk(File $file, $offset, $length)
    {
        if ($file->transfer->options['encryption']) {
            $offset=$offset/Config::get('upload_chunk_size')*Config::get('upload_crypted_chunk_size');
        }

        $out=self::run('fs_readChunk "'.$file->uid.'" '.$offset.' '.$length);
        if ($out['status']!=0) {
            throw new StorageFilesystemFileNotFoundException($file->uid, $file);
        } else {
            $data = $out['stdout'];
        }

        if ($data === false) {
            return null;
        }

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
    public static function writeChunk(File $file, $data, $offset = null)
    {
        $chunk_size = strlen($data);
        $size = $file->transfer->options['encryption'] ? $file->encrypted_size : $file->size;

        $out=self::run('fs_writeChunk "'.$file->uid.'" '.$offset.' '.$chunk_size.' '.$size, $data);
        if ($out['status']!=0) {
            throw new StorageFilesystemCannotWriteException('writeChunk( '.$file->uid, $file);
        } else {
            $written = $out['stdout'];
            if ($written != $chunk_size) {
                Logger::info('writeChunk() Can not write to : '.$file->uid.' '.$written);
                Logger::info('writeChunk() cmd: fs_writeChunk "'.$file->uid.'" '.$offset.' '.$chunk_size.' '.$size);
                throw new StorageFilesystemCannotWriteException('writeChunk( '.$file->uid, $file, $data, $offset, $written);
            }
            return array(
            'offset' => $offset,
            'written' => $written
        );
        }
    }
    
    /**
     * Handles file completion checks
     *
     * @param File $file
     */
    public static function completeFile(File $file)
    {
        self::setup();
        $size = $file->transfer->options['encryption'] ? $file->encrypted_size : $file->size;

        $out=self::run('fs_completeFile "'.$file->uid.'" '.$size);
    }
    
    /**
     * Deletes a file
     *
     * @param File $file
     *
     * @throws StorageFilesystemCannotDeleteException
     */
    public static function deleteFile(File $file)
    {
        $out=self::run('fs_deleteFile "'.$file->uid.'"');
        if ($out['status']!=0) {
            throw new StorageFilesystemCannotDeleteException($file->uid, $file);
        }
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
    public static function storeWholeFile(File $file, $source_path)
    {
        return self::writeChunk($file, file_get_contents($source_path), 0);
    }

    public static function getStream(File $file)
    {
        StorageFilesystemExternalStream::ensureRegistered();
        $path = self::makeCustomStreamPath( $file, "StorageFilesystemExternalStream" );
        $fp = fopen($path, "r+");
        return $fp;
    }
}
