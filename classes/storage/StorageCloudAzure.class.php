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

require_once dirname(__FILE__).'/../../optional-dependencies/azure/vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
//use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\GetBlobOptions;
use MicrosoftAzure\Storage\Common\Exceptions\InvalidArgumentTypeException;
use MicrosoftAzure\Storage\Common\Internal\Resources;
use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;
use MicrosoftAzure\Storage\Common\Models\RetentionPolicy;
use MicrosoftAzure\Storage\Common\Models\ServiceProperties;
use MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper;

/**
 *  Gives access to a file on azure cloud
 *
 *  This class stores the chunks that are sent as individual blobs
 *
 */
class StorageCloudAzure extends StorageFilesystem
{
    public static function getBlobService()
    {
        $connectionString = ConfigPrivate::get('cloud_azure_connection_string');
        $blobClient = BlobRestProxy::createBlobService($connectionString);
        return $blobClient;
    }
    
    public static function getOffsetWithinBlob($offset)
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        return ($offset % $file_chunk_size);
    }
    
    public static function getBlobName($offset)
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

        $container_name = $file->uid;
        $blob_name      = self::getBlobName($offset);
        
        try {
            $az = self::getBlobService();
            $res = $az->getBlob($container_name, $blob_name);
            $data = stream_get_contents($res->getContentStream());

            if ($data === false) {
                return null;
            }
            return $data;
        } catch (ServiceException $e) {
            $msg = 'Azure: readChunk() Can not read to blob: ' . $blob_name . ' offset ' . $offset
                 . $e->getErrorText() . ' ' . $e->getErrorReason();
            Logger::info($msg);
            if (is_a($e, 'ConfigMissingParameterException')) {
                Logger::error("NOTE: MISSING PARAMETER IN CONFIG FILE");
                $msg .= "  NOTE: MISSING PARAMETER IN CONFIG FILE";
            }
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
        $container_name = $file->uid;
        $blob_name      = self::getBlobName($offset);

        try {
            $az = self::getBlobService();
            
            try {
                $az->createBlockBlob($container_name, $blob_name, $data);
            } catch (ServiceException $e) {
                if ($e->getCode() == 404) {
                    // make container and try again
                    $opts = new CreateContainerOptions();
                    $az->createContainer($container_name, $opts);
                    $az->createBlockBlob($container_name, $blob_name, $data);
                }
            }
            return array(
                'offset' => $offset,
                'written' => $chunk_size
            );
        } catch (ServiceException $e) {
            $msg = 'Azure: writeChunk() Can not write to blob: ' . $blob_name . ' offset ' . $offset
               . $e->getErrorText() . ' ' . $e->getErrorReason();
            Logger::info($msg);
            if (is_a($e, 'ConfigMissingParameterException')) {
                Logger::error("NOTE: MISSING PARAMETER IN CONFIG FILE");
                $msg .= "  NOTE: MISSING PARAMETER IN CONFIG FILE";
            }
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
        $container_name = $file->uid;

        try {
            $az = self::getBlobService();
            
            $az->deleteContainer($container_name);
        } catch (ServiceException $e) {
            $msg = 'Azure: deleteFile() Can not delete container: ' . $container_name
                 . ' ' . $e->getMessage();
            Logger::info($msg);
            throw new StorageFilesystemCannotDeleteException($msg, $file);
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
        StorageCloudAzureStream::ensureRegistered();
        $path = "StorageCloudAzureStream://" . $file->uid;
        $fp = fopen($path, "r+");
        return $fp;
    }
}
