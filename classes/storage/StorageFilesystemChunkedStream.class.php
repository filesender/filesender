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
 * Allow reading a chunked file as a normal php stream
 * only in order start to finish reading is supported as yet.
 */
class StorageFilesystemChunkedStream {

    protected $offset = 0;
    protected $uid    = null;
    protected $file   = null;
    protected $fh     = null;
    protected $currentChunkFile = null;
    protected $gameOver = false;
    
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->offset = 0;
        $this->uid = $url["host"];
        $this->file = File::fromUid($this->uid);
        return true;
    }


    function stream_read($count) {
        $file   = $this->file;
        $offset = $this->offset;

        if( $this->gameOver )
            return FALSE;
        
        $file_path = StorageFilesystem::buildPath($file).$file->uid;
	$chunkFile = StorageFilesystemChunked::getChunkFilename($file_path,$offset);


        if( strcmp( $this->currentChunkFile, $chunkFile )) {

            // if we try to open the file after the last chunk then we return FALSE
            $fh = fopen($chunkFile,'r');
            if( $fh == FALSE ) {
                $this->gameOver = true;
                return FALSE;
            }
            
            $rc = fseek($fh,StorageFilesystemChunked::getOffsetWithinChunkedFile($file_path,$offset));
            if( $rc == -1 ) {
                $this->gameOver = true;
                if( $this->fh )
                    fclose($this->fh);
                return FALSE;
            }
            
            $this->fh = $fh;
            $this->currentChunkFile = $chunkFile;
        }

        $data = fread( $this->fh, $count );
        if( $data == FALSE ) {
            $this->gameOver = true;
            if( $this->fh )
                fclose($this->fh);
            return FALSE;
        }
        
        $this->offset += strlen($data);
        return $data;
    }

    function stream_eof() {
        return $this->offset >= $this->file->size;
    }

    static function ensureRegistered() {
        // this happens when the file is parsed
    }
};

stream_wrapper_register("StorageFilesystemChunkedStream", "StorageFilesystemChunkedStream")
or die("Failed to register protocol");
