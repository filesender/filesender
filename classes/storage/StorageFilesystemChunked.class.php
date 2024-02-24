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
 *     Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *     Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *     Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
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
 * Generic Chunk exception so we can throw a specific exception with a message on failed writes
 */
class ChunkWriteException extends \Exception {}


/**
 *  Gives access to a file on the filesystem
 *
 *  This class stores the chunks that are sent as individual files
 *
 *  One use case for this is to use FUSE to efficiently hand the data off to EOS
 *  https://twiki.cern.ch/twiki/bin/view/EOS/WebHome
 */
class StorageFilesystemChunked extends StorageFilesystem
{
    /**
     * getOffsetWithinChunkedFile()
     *
     * @param int $offset
     * @return int
     */
    public static function getOffsetWithinChunkedFile($offset)
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        return ($offset % $file_chunk_size);
    }

    /**
     * Generate the filename for the chunk
     *
     * @param string $filePath
     * @param int $offset
     * @return string
     */
    public static function getChunkFilename($filePath, $offset)
    {
        $file_chunk_size = Config::get('upload_chunk_size');
        $offset = $offset - ($offset % $file_chunk_size);
        return $filePath.'/'.str_pad($offset, 24, '0', STR_PAD_LEFT);
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
        if ($file->transfer->is_encrypted) {
            $offset=$offset/Config::get('upload_chunk_size')*Config::get('upload_crypted_chunk_size');
        }

        $filePath = self::buildPath($file);
        $chunkFile = self::getChunkFilename($filePath, $offset);

        if (!file_exists($chunkFile)) {
            Logger::error('readChunk() failed: '.$chunkFile.' does not exist.');
            throw new StorageFilesystemFileNotFoundException($chunkFile, $file);
        }

        $data = self::file_get_contents($chunkFile);
        if ($data === false) {
            Logger::error('readChunk() failed: '.$chunkFile.' failed to read file.');
            throw new StorageFilesystemCannotReadException($filePath, $file);
        }
        return $data;
    }

    /**
     * Return the path to the directory that holds the file chunks
     * @param File $file
     * @return string
     */
    public static function buildPath(File $file, $fullPath = true )
    {
        return parent::buildPath($file).$file->uid;
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
        
        $chunkSize = \strlen($data);
        $filePath = self::buildPath($file);

        // If the user is doing something with FUSE
        // then they might not want to check disk space.
        if (!Config::get('storage_filesystem_ignore_disk_full_check')) {
	        // Check that there is enough free space on the storage
	        $freeSpace = disk_free_space(self::$path);
	        if ($freeSpace <= $chunkSize) {
	            throw new StorageNotEnoughSpaceLeftException($chunkSize);
	        }
        }

        if (self::file_exists($filePath) === false) {
            mkdir($filePath, 0770, true);
        }

        $chunkFile = self::getChunkFilename($filePath, $offset);
        $validUpload = false;
        for ($attempt = 1; $attempt <= Config::get('storage_filesystem_write_retry'); $attempt++) {
            $isLastAttempt = $attempt >= Config::get('storage_filesystem_write_retry');
            try {
                // Open file, go to next try if it fails
                $fh = \fopen($chunkFile, 'wb');
                if ($fh === false) {
                    throw new ChunkWriteException('fopen() failed');
                }

                // Write file, flush buffers then unlock
                $written = \fwrite($fh, $data, $chunkSize);

                // Close file and move to next iteration if it fails.
                if (\fclose($fh) === false) {
                    throw new ChunkWriteException('fclose() failed');
                }

                // Check that the right amount of data was written and move to next iteration if it fails
                if ($chunkSize != $written) {
                    throw new ChunkWriteException('chunk_size != bytes written');
                }

                // Clear cached values and check the chunk file now exists, otherwise we retry from the beginning
                if (!self::file_exists($chunkFile)) {
                    throw new ChunkWriteException('file does not exist after write');
                }

                // Check file size
                if ($chunkSize != self::filesize($chunkFile)) {
                    throw new ChunkWriteException('chunk_size != filesize()');
                }

                if (Config::get('storage_filesystem_hash_check')) {
                    // Hash the file to make sure it actually exists and matches the written data
                    if (!self::verifyChecksum('md5', $data, $chunkFile)) {
                        throw new ChunkWriteException('checksum validation failed');
                    }
                }

                $validUpload = true;
                // Passed all the validation so I guess let's get out of this loop
                break;
            } catch (ChunkWriteException $e) {
                if ($isLastAttempt) {
                    Logger::error("writeChunk() failed: {$chunkFile} {$e->getMessage()}");
                    // Re-throw any StorageFilesystemCannotWriteException so that we can add extra information
                    throw new StorageFilesystemCannotWriteException($filePath, $file, $data, $offset, $written);
                } else {
                    Logger::debug("writeChunk() failed: {$chunkFile} {$e->getMessage()}");
                }
            }
            usleep(Config::get('storage_filesystem_retry_sleep'));
        }

        // Just in case we get through all of our attempts and don't hit the try/catch
        if ($validUpload === false && $isLastAttempt) {
            Logger::error("writeChunk() failed: {$chunkFile} exceeded retry attempts");
            throw new StorageFilesystemCannotWriteException($filePath, $file, $data, $offset, $written);
        }

        // Don't reach the end if the chunk is invalid.
        if ($validUpload === false) {
            Logger::error("writeChunk() failed: {$chunkFile} was an invalid upload");
            throw new StorageFilesystemCannotWriteException($filePath, $file, $data, $offset, $written);
        }

        return array(
            'offset' => $offset,
            'written' => $written
        );
    }

    /**
     * Helper method for file_get_contents()
     * @param resource $handle
     * @param string $data
     * @param int $size
     * @return int
     */
    public static function file_get_contents($path)
    {
        for ($attempt = 0; $attempt < Config::get('storage_filesystem_read_retry'); $attempt++) {
            $data = file_get_contents($path);
            if ($data !== false) {
                return $data;
            }
            usleep(Config::get('storage_filesystem_retry_sleep'));
        }
        return false;
    }

    /**
     * Helper method for file_exists()
     * @param string $path
     * @return bool
     */
    public static function file_exists($path)
    {
        \clearstatcache(true, $path);
        return \file_exists($path);
    }

    /**
     * Helper method for filesize()
     * @param string $path
     * @return int|false
     */
    public static function filesize($path)
    {
        \clearstatcache(true, $path);
        return \filesize($path);
    }

    /**
     * Helper method for hash_file()
     * @param string $path
     * @return int|false
     */
    public static function hash_file($algo, $path)
    {
        return @\hash_file($algo, $path);
    }

    /**
     * Helper method to validate the integrity of a file vs the incoming stream
     * @param string $algo
     * @param string $stream
     * @param string $path
     * @return bool
     */
    public static function verifyChecksum($algo, $stream, $path)
    {
        $fileHash = self::hash_file($algo, $path);
        if ($fileHash === false) {
            Logger::error("verifyChecksum() unable to hash file: $path");
            return false;
        }

        $streamHash = \hash($algo, $stream);
        if ($fileHash === $streamHash) {
            return true;
        }

        Logger::error("verifyChecksum() checksums do not match. file: $path file_hash: $fileHash stream_hash: $streamHash");
        return false;
    }

    /**
     * Handles file completion checks
     *
     * @param File $file
     */
    public static function completeFile(File $file)
    {
        self::setup();
        $filePath = self::buildPath($file);
        $onDiskSize = self::calculateOnDiskFileSize($file);
        $expectedSize = $file->size;
        if ($file->transfer->is_encrypted) {
            $expectedSize = $file->encrypted_size;
        }

        if ($onDiskSize != $expectedSize) {
            Logger::error('completeFile('.$file->uid.') size mismatch. expected_size:'.$expectedSize.' ondisk_size:'.$onDiskSize);
            throw new FileIntegrityCheckFailedException($file, 'Expected size was '.$expectedSize.' but size on disk is '.$onDiskSize);
        }
    }

    /**
     * Calculates the size of a File's directory
     *
     * @param File $file
     *
     * @return int
     */
    public static function calculateOnDiskFileSize(File $file)
    {
        $path = self::buildPath($file);
        $totalSize = 0;
        foreach (new DirectoryIterator($path) as $fileChunk) {
            if ($fileChunk->isFile()) {
                $totalSize += $fileChunk->getSize();
            }
        }
        return $totalSize;
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
        $filePath = self::buildPath($file);
        Filesystem::deleteTreeRecursive($filePath, $file);
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

    /**
     * Get a resource stream (used with the archiver to generate tar/zip files)
     *
     * @param File $file
     * @return resource
     */
    public static function getStream(File $file)
    {
        StorageFilesystemChunkedStream::ensureRegistered();
        $path = "StorageFilesystemChunkedStream://" . $file->uid;
        $fp = \fopen($path, "r+");
        stream_set_chunk_size($fp, Config::get('upload_chunk_size'));
        return $fp;
    }
}
