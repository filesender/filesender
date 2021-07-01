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

if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

require_once dirname(__FILE__).'/../../optional-dependencies/s3/vendor/autoload.php';
use Aws\S3\S3Client;

/**
 *  Gives access to a file on azure cloud
 *
 *  This class stores the chunks that are sent as individual blobs
 *
 */
class StorageCloudS3 extends StorageFilesystem
{
    private static $client = null;

    public static function getClient()
    {
        if (self::$client) {
            return self::$client;
        }
        
        self::$client = S3Client::factory(array(
            'region'   => Config::get('cloud_s3_region'),
            'version'  => Config::get('cloud_s3_version'),
            'endpoint' => Config::get('cloud_s3_endpoint'),
            'use_path_style_endpoint' => Config::get('cloud_s3_use_path_style_endpoint'),
            'credentials' => array(
                'key'    => Config::get('cloud_s3_key'),
                'secret' => Config::get('cloud_s3_secret'),
            )
        ));
        return self::$client;
    }
    
    public static function getOffsetWithinBlob($offset)
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        return ($offset % $file_chunk_size);
    }
    
    public static function getObjectName($offset)
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        $offset = $offset - ($offset % $file_chunk_size);
        return str_pad($offset, 24, '0', STR_PAD_LEFT);
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

        $bucket_name = $file->uid;
        $object_name = self::getObjectName($offset);
        
        try {
            $client = self::getClient();

            $result = $client->getObject(array(
                'Bucket' => $bucket_name,
                'Key'    => $object_name,
            ));
            
            $data = $result['Body'];
            if ($data === false) {
                return null;
            }
            return $data;
        } catch (ServiceException $e) {
            $msg = 'S3: readChunk() Can not read to object_name: ' . $object_name . ' offset ' . $offset;
            Logger::info($msg);
            throw new StorageFilesystemCannotReadException($msg);
        }

        return null;
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
        $chunk_size     = strlen($data);
        $bucket_name = $file->uid;
        $object_name = self::getObjectName($offset);

        try {
            $client = self::getClient();

            $client->createBucket(array(
                'Bucket' => $bucket_name,
            ));

            $result = $client->putObject(array(
                'Bucket' => $bucket_name,
                'Key'    => $object_name,
                'Body'   => $data,
            ));
            
            return array(
                'offset' => $offset,
                'written' => $written
            );
        } catch (Exception $e) {
            $msg = 'S3: writeChunk() Can not write to object_name: ' . $object_name . ' offset ' . $offset;
            Logger::info($msg);
            throw new StorageFilesystemCannotWriteException($msg);
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
        $file_path = self::buildPath($file).$file->uid;
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
        $file_path = self::buildPath($file).$file->uid;
        $bucket_name = $file->uid;
        $object_name = self::getObjectName($offset);

        try {
            $client = self::getClient();

            $objects = $client->getIterator('ListObjects', array('Bucket' => $bucket_name));

            foreach ($objects as $object) {
                $result = $client->deleteObject(array(
                    'Bucket' => $bucket_name,
                    'Key'    => $object['Key']
                ));
            }
            $result = $client->deleteBucket(array(
                'Bucket' => $bucket_name,
            ));
        } catch (Exception $e) {
            Logger::info('deleteFile() error ' . $e);
            throw new StorageFilesystemCannotDeleteException($file_path, $file);
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
        StorageCloudS3Stream::ensureRegistered();
        $path = "StorageCloudS3Stream://" . $file->uid;
        $fp = fopen($path, "r+");
        stream_set_chunk_size($fp, Config::get('upload_chunk_size'));
        return $fp;
    }
}
