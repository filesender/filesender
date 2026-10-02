<?php

/*
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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * Handle creating Transactions.
 *
 *
 */
class TestDatabaseTransfers
{
    /**
     * For bringing in data
     */
    private $mimeinfo = null;
    protected $creator = null;
    protected $nextmsgid = 1;

    
    /**
     * @param $creator is the TestDatabaseCreator object. Use
     * TestDatabaseCreator::getTestDatabaseTransfers() to get an object of this class.
     *
     */
    public function __construct($creator)
    {
        $this->creator = $creator;
        if (!function_exists('finfo_open')) {
            throw new Exception('File Info PHP extention is required but not found');
        }
        $this->mimeinfo = finfo_open(FILEINFO_MIME_TYPE);
    }

    public function output($msg)
    {
        $this->creator->output($msg);
    }

    /**
     * The number of files in the testing filesystem
     */
    public static function getFileCount()
    {
        return 50;
    }

    /**
     * Create a new transfer
     *
     * @param string path The path of the file to upload
     * @param string subject (optional) The subject for the transfer
     * @param string message (optional) message for transfer
     * @param array options (optional) options for transfer
     */
    public function createTransfer(
        $path,
                             $subject = null,
                             $message = 'no message given',
                             $options = array('encryption' => false )
    ) {
        $expires = Config::get('default_transfer_days_valid');
        $transfer = Transfer::create(time() + $expires * 24 * 3600);
        if ($subject == null) {
            $subject = 'subject of message number ' . $this->nextmsgid++;
        }
        if ($subject) {
            $transfer->subject = $subject;
        }
        if ($message) {
            $transfer->message = $message;
        }
        if ($options) {
            $transfer->options = $options;
        }
        $transfer->save();
        $transfer->start();
        //        $this->output('Empty transfer created');
        $this->transferAddFile($transfer, $path);
        $transfer->isUploading();
        return $transfer;
    }
    
    /**
     * Add the file at path to the transfer
     *
     * @param obj $transfer to add file to
     * @param string $path of the file to add to transfer
     */
    public function transferAddFile($transfer, $path)
    {
        if (strlen($path)) {
            if ($path[0] != '/') {
                $dataset = __DIR__ . '/../../../unittests/data/dataset/';
                $path = $dataset . $path;
            }
        }
        
        $path = realpath($path);
        $mime = finfo_file($this->mimeinfo, $path);
        $file = $transfer->addFile(basename($path), filesize($path), $mime);
        
        //        $this->output('Adding '.$path.' ('.$mime.', '.filesize($path).' bytes) ... ');
        
        if (Storage::supportsWholeFile()) {
            Storage::storeWholeFile($file, $path);
        } else {
            $chunk_size = Config::get('upload_chunk_size');
            if ($fh = fopen($path, 'rb')) {
                for ($offset=0; $offset<=$file->size; $offset+=$chunk_size) {
                    $data = fread($fh, $chunk_size);
                    $file->writeChunk($data, $offset);
                    $this->output('Chunk '.$offset.'..'.($offset + $chunk_size).' added');
                }
                
                fclose($fh);
            } else {
                throw new CoreCannotReadFileException($path);
            }
        }
        
        $file->complete();
        //        $this->output('Done for '.$path);
        return $file;
    }

    /**
     * Perform a given number of transfers as the current user.
     * This loops through the available fileN pool many times until
     * the desired number of single file transfers have been performed.
     *
     * @param targetTransfers   the number of transfers to create
     * @param filesPerTransfer  the number of files to add to each created transfer
     * @param addRecipients     a callback to add however many recipients to each transfer
     * @param callbackObjects   array of objects to call the visitTransfer method of
     *                          these are called in foreach() order
     */
    public function performTransfers(
        $targetTransfers,
                               $filesPerTransfer = 1,
                               $addRecipients = null,
                               $callbackObjects = array()
    ) {
        $this->output("Performing $targetTransfers Transfers with $filesPerTransfer file...");

        $fileCount = $this->getFileCount();
        for ($i = 0; $i < $targetTransfers; $i++) {
            if (!($i%500)) {
                $this->output("progress $i of $targetTransfers Transfers with $filesPerTransfer files...");
            }
            $lastFile = null;
            $numbers = range(0, $fileCount-1);
            shuffle($numbers);
            $numbers = array_slice($numbers, 0, $filesPerTransfer);
            $fn = array_shift($numbers);
            $path = 'file' . $fn;
            $transfer = $this->createTransfer(
                $path,
                                               'testdriver test',
                                               'testdriver',
                                               array('encryption' => false,
                                                     'get_a_link' => false,
                                                     'email_upload_complete' => true )
            );
            for ($j = 1; $j < $filesPerTransfer; $j++) {
                $fn = array_shift($numbers);
                $path = 'file' . $fn;
                $this->transferAddFile($transfer, $path);
            }
            if ($addRecipients == null) {
                $recipient = $transfer->addRecipient('tester@localhost.localdomain');
            } else {
                $recipient = call_user_func(
                    array($addRecipients, 'visitTransfer'),
                                            $transfer
                );
            }
            $transfer->makeAvailable();

            /*
             * Hand it over to the callbacks to do their thing
             */
            $files = $transfer->files;
            $f = reset($files);
            foreach ($callbackObjects as $cbobj) {
                call_user_func(
                    array($cbobj, 'visitTransfer'),
                               $transfer,
                    $f,
                    $recipient
                );
            }
        }
    }
}
