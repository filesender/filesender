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
    
    public static function getOffsetWithinBlob( $offset )
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        return ($offset % $file_chunk_size);
    }

    public static function usingCustomBucketName( File $file )
    {
        if ($file && $file->transfer && $file->transfer->getOption(TransferOptions::STORAGE_CLOUD_S3_BUCKET)) {
            $v = $file->transfer->options[TransferOptions::STORAGE_CLOUD_S3_BUCKET];
            if( $v && $v != '' ) {
                return true;
            }
        }
        return false;
    }
    
    public static function getObjectName( File $file, $offset )
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        $offset = $offset - ($offset % $file_chunk_size);
        $object_name = str_pad($offset, 24, '0', STR_PAD_LEFT);

        if( self::usingCustomBucketName( $file ) ) {
            return $file->uid . '/' . $object_name;
        }
        return $object_name;
    }

    public static function getBucketName(File $file)
    {
        if( self::usingCustomBucketName( $file ) ) {
            return $file->transfer->options[TransferOptions::STORAGE_CLOUD_S3_BUCKET];
        }
        return $file->uid;
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

        $bucket_name = self::getBucketName( $file );
        $object_name = self::getObjectName( $file, $offset );

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

        $bucket_name = self::getBucketName( $file );
        $object_name = self::getObjectName( $file, $offset );
        
        try {
            $client = self::getClient();

            if( !self::usingCustomBucketName( $file )) {
                $client->createBucket(array(
                    'Bucket' => $bucket_name,
                ));
            }

            $result = $client->putObject(array(
                'Bucket' => $bucket_name,
                'Key'    => $object_name,
                'Body'   => $data,
            ));
            
            return array(
                'offset' => $offset,
                'written' => $chunk_size
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

        $offset = 0;
        $bucket_name = self::getBucketName( $file );
        $object_name = self::getObjectName( $file, $offset );
	$bulk_delete = Config::get('cloud_s3_bulk_delete');
	$bulk_size = Config::get('cloud_s3_bulk_size');

	Logger::debug('CloudS3 deleteFile(), file_path: ' . $file_path . ' bucket_name: ' . $bucket_name . ' object_name: ' . $object_name . ' bulk: ' . $bulk_delete . '/' . $bulk_size);
        
        try {
            $client = self::getClient();

            if( !self::usingCustomBucketName( $file ) ) {
                $objects = $client->getIterator('ListObjects', array('Bucket' => $bucket_name));
            } else {
                $objects = $client->getIterator('ListObjects', array('Bucket' => $bucket_name, 'Prefix' => $file->uid));    
            }

	    if( $bulk_delete ) {

	        // Build bulk request the way AWS client wants it
                foreach ($objects as $object) {
                    $delete_queue[] = [
                        'Key' => $object['Key'],
                    ];
                }

		if( $delete_queue ) {

		    // Create batches of requested batchsize
                    $chunked_queue = array_chunk($delete_queue, $bulk_size);
                    Logger::debug('bulk has ' . count($chunked_queue) . ' requests');


                    // Perform actual chunk removal
                    foreach($chunked_queue as $delete_batch) {

                        Logger::debug('bulk deleting ' . count($delete_batch) . ' chunks');
                        $result = $client->deleteObjects([
                           'Bucket' => $bucket_name,
                           'Delete' => [
                               'Objects' => $delete_batch,
                           ],
                        ]);
                    }
		}

            } else {

		Logger::debug('Executing non-bulk delete');
                foreach ($objects as $object) {
                    $result = $client->deleteObject(array(
                        'Bucket' => $bucket_name,
                        'Key'    => $object['Key']
                    ));
                }
            }
            
            if( !self::usingCustomBucketName( $file ) ) {
                $result = $client->deleteBucket(array(
                    'Bucket' => $bucket_name,
                ));
            }
        } catch (Exception $e) {
            if (preg_match('/NoSuchBucket/', $e)) {
                // S3 backend has returned a NoSuchBucket error, this happens when we have already
                // deleted this file (when using per-file buckets) or the daily bucket was empty and has
                // already been deleted (when using daily buckets).
                // 
                // Usually this happens when Transfer was deleted when it expired and cron.php re-deletes
                // all files when it's purging the transfer from database.
            } else {
                Logger::info('deleteFile() error ' . $e);
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            }
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



    /**
     * Bucket maintenance operations for when using Daily Buckets
     * 
     * Intended to be called from cron.php as a part of regular daily maintenance
     * 
     * Can also be called via scripts/task/S3bucketmaintenance.php (for example
     * when taking Daily Buckets into use and new buckets need to be created asap)
     * 
     * Lists current buckets in S3 storage
     * Creates new daily buckets for today + 14 days (if they don't already exist)
     * Removes old daily buckets (if they are empty)
     * 
     * @param bool $verbose
     * 
     * @return bool
     * 
     * @throws Exception
     */
    public static function dailyBucketMaintenance($verbose = false)
    {

        if (!Config::get('cloud_s3_use_daily_bucket')) {
            throw new Exception('Function StorageCloudS3::dailyBucketMaintenance was called but configuration option cloud_s3_use_daily_bucket is not set to true!');
        }

        // Array of New Buckets (should be created if they don't exist)
        $newbuckets = array();

	// Array of Old Buckets (should be checked for contents)
        $oldbuckets = array();


        $bucketprefix = "";
        if (Config::get('cloud_s3_bucket_prefix')) {
            $bucketprefix = Config::get('cloud_s3_bucket_prefix');
        }


        // Initialize array of new buckets with today + 14 next days
        // If these buckets are found in the scan, they will be removed from this array
        // After scan, all buckets still remaining in array will be created
        $timestamp = time();
        for ($i = 0; $i <= 14; $i++) {
            $newbuckets["" . $bucketprefix . date("Y-m-d", $timestamp)] = 1;
            $timestamp += 60*60*24;
        }


        $client = self::getClient();

        // Fetch list of all S3 buckets we can see
        $result = $client->getIterator('ListBuckets', array());

        $name = "";
        $bucket = "";

        foreach ($result as $bucket) {

            $name = $bucket["Name"];

            if ($verbose) echo "Saw Bucket in S3: $name";
            if (preg_match('/^' . preg_quote($bucketprefix, '/') . '\d\d\d\d-\d\d-\d\d$/', $name)) {
                // Matches our prefix + date -> should be filesender's bucket
                if (isset($newbuckets[$name])) {
                    unset($newbuckets[$name]);
                    if ($verbose) echo " (future bucket)";
                } else {
                    $oldbuckets[$name] = 1;
                }
            } else {
                if ($verbose) echo " (not ours)";
            }

            if ($verbose) echo "\n";

        }

        foreach (array_keys($newbuckets) as $name) {

            if ($verbose) echo "Creating new bucket: $name\n";
            $client->createBucket(array(
                'Bucket' => $name,
            ));

        }

        foreach (array_keys($oldbuckets) as $name) {

            $result = $client->ListObjectsV2(array(
                'Bucket' => $name,
                'MaxKeys' => 2
            ));

            if ($verbose) echo "Checked old bucket " . $result["Name"] . ", it has " . $result["KeyCount"] . " objects.";

            // If the bucket name matches what we asked for (= no error on request)
            // and it has 0 objects, it should be safe to delete..
            if ($result["Name"] == $name && $result["KeyCount"] == 0) {
                if ($verbose) echo " (will remove)";
                $client->deleteBucket(array(
                    'Bucket' => $name,
                ));
            }
            if ($verbose) echo "\n";

        }

        return true;
    }

    /**
     * Add custom bucket name info to transfer options
     * 
     * @param array $options
     * 
     * @return array new options
     * 
     */
    public static function augmentTransferOptions( $options )
    {
        if( strtolower(Config::get('storage_type')) == 'clouds3' ) {
            if (Config::get('cloud_s3_use_daily_bucket')) {
                $options[TransferOptions::STORAGE_CLOUD_S3_BUCKET] = "";
                $v = Config::get('cloud_s3_bucket_prefix');
                if( $v && $v != '' ) {
                    $options[TransferOptions::STORAGE_CLOUD_S3_BUCKET] = $v;
                }
                $options[TransferOptions::STORAGE_CLOUD_S3_BUCKET] .= date("Y-m-d");
            } else {
                $v = Config::get('cloud_s3_bucket');
                if( $v && $v != '' ) {
                    $options[TransferOptions::STORAGE_CLOUD_S3_BUCKET] = $v;
                }
            }
        }

        return $options;
    }
}
