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

require_once(FILESENDER_BASE.'/lib/PHPZipStreamer/src/ZipStreamer.php');
require_once(FILESENDER_BASE.'/lib/PHPZipStreamer/src/lib/Count64.php');


/**
 * Stream multiple files at once as an uncompressed ZIP archive. The archive is created on-the-fly and does not require
 * large files to be loaded into memory all at once.
 * 
 * See http://www.pkware.com/documents/casestudies/APPNOTE.TXT for a full specification of the .ZIP file format.
 * Code inspired by Paul Duncan's (@link http://pablotron.org/software/zipstream-php/ ZipStream-PHP).
 */
class Zipper {
    
    /* 
     * Array of File 
     * Containing :
     *      ['data'] => $file       // the file
     *      ['content'] => array(   // Informations about the files
     *          'crc' => CRC32
     *          'offset' => offset
     *          'timestamp' => timestamp
     *      )
     */
    private $files;
    
    
    /**
     * Constuctor of Zipper class
     */
    public function __construct(){
        $this->files = array();
    }

    
    // ------------------------------------------------------------------------
    // Business functions
    // ------------------------------------------------------------------------
    
    
    /*
     * Adds a file to be sent as part of the ZIP. Nothing is sent at this stage.
     * $file must be a File DBObject 
     * 
     * @param File $file: the file to add
     */
    public function addFile(File $file){
        $this->files[$file->id]['data'] = $file;
    }

    /**
     * Creates a ZIP archive on-the-fly and streams it to the client. 
     * <b>The files in the archive are not compressed.</b>
     */
    public function sendZip($recipient = null, $withHeaders = true)
    {
        $zip = new ZipStreamer\ZipStreamer();

        // set headers
        $fuid = substr(hash('sha1', implode('+', array_keys($this->files))), -8);
        $file = reset($this->files);
        $tid = $file['data']->transfer_id;
        $filename = 'transfer_' . $tid . '_files_' . $fuid . '.zip';
        if( $withHeaders ) {
            $zip->sendHeaders( $filename, "application/octet-stream" );
        }
        
        // send each file
        foreach ($this->files as $k => $data) {
            $file = $data['data'];
            $transfer = $file->transfer;

            if($recipient)
                Logger::logActivity(LogEventTypes::DOWNLOAD_STARTED, $file, $recipient);
            
            // Set up metadata and send the local header.
            $name = preg_replace('/^\\/+/', '', $file->name); // Strip leading slashes from filename.
            $name = preg_replace('/\\.\\.\\//', '', $name);   // strip ../
            $name = preg_replace('/\\/\\.\\./', '', $name);   // strip /..
            
            $stream = $file->getStream();
            $zip->addFileFromStream($stream, $name);
            fclose($stream);
        }

        // finish up and log everything
        $zip->finalize();
        if($recipient) foreach ($this->files as $data) {
            $file = $data['data'];
            Logger::logActivity(LogEventTypes::DOWNLOAD_ENDED, $file, $recipient);
        }
        
        // ok
        return true;
    }

    // ------------------------------------------------------------------------
    // Utilities functions
    // ------------------------------------------------------------------------
    
    
    /**
     * Returns the an extimate of the total file size of the ZIP archive.
     *  depending on the number and sizes of the files and whether
     * or not the ZIP64 format is being used.
     * 
     * @return int: the total filesize of the archive
     */
    public function calculateTotalFileSize() {
        $fileSize = 22; // Size of the end-of-file TOC record.

        foreach ($this->files as $data) {
            $file = $data['data'];
            $fileSize += 92 + strlen($file->name) * 2; // Size of the local file header, descriptor and per-file CDR entry.
            $fileSize += $file->size; // File contents size.

            $fileSize += 48; // Extra file data for ZIP64 format.
        }

        $fileSize += 76; // Extra end-of-file ZIP64 information.

        return $fileSize;
    }
}
