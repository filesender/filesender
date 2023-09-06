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


// generating tar
require_once(FILESENDER_BASE.'/lib/vendor//barracudanetworks/archivestream-php/src/Archive.php');
require_once(FILESENDER_BASE.'/lib/vendor//barracudanetworks/archivestream-php/src/TarArchive.php');
require_once(FILESENDER_BASE.'/lib/vendor//barracudanetworks/archivestream-php/src/ZipArchive.php');
require_once(FILESENDER_BASE.'/lib/vendor/autoload.php');

/**
 * Stream multiple files at once as an uncompressed ZIP archive. The archive is created on-the-fly and does not require
 * large files to be loaded into memory all at once.
 *
 * See http://www.pkware.com/documents/casestudies/APPNOTE.TXT for a full specification of the .ZIP file format.
 * Code inspired by Paul Duncan's (@link http://pablotron.org/software/zipstream-php/ ZipStream-PHP).
 */
class Archiver
{
    
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
    private $archive_format;
    
    /**
     * Constuctor
     */
    public function __construct( $archive_format = 'zip' )
    {
        $this->files = array();
        $this->archive_format = $archive_format;
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
    public function addFile(File $file)
    {
        $this->files[$file->id]['data'] = $file;
    }


    public function getZipSize($filename,$options) {

        $contentsz = 0;
        $tfn = tempnam( Filesystem::getTempDirectory(), 'szf');
        $outstream = fopen($tfn,'w');

        $options->setSendHttpHeaders(false);
        $options->setOutputStream($outstream);
        $archive = new ZipStream\ZipStream($filename . ".zip", $options);        

        // send each file
        foreach ($this->files as $k => $data) {
            $file = $data['data'];
            $fileopts = array();
            $transfer = $file->transfer;
            $archivedName = $this->getArchivedFileName( $file );
            $contentsz += $file->size;
            $sz = $file->size;

            $fileopts = new ZipStream\Option\File();
            $fileopts->defaultTo($options);
            $fileopts->setMethod(ZipStream\Option\Method::STORE());
            
            $file = new ZipStream\File($archive, $archivedName, $fileopts);

            // We do not really want to add the file data
            // just the size and metadata
            // so the zip tells us how big that will be.
            $file->bits |= ZipStream\File::BIT_ZERO_HEADER;
            $file->len = new ZipStream\Bigint($sz);
            $file->zlen = $file->len;
            $file->addFileHeader();
            $file->addFileFooter();
        }

        $archive->finish();        

        fflush($outstream);
        $ret = $contentsz + filesize($tfn);
        fclose($outstream);
        unlink($tfn);

        return $ret;
    }
        
    /**
     * This is a bit sneaky, we create a temporary tarball to include
     * all the byte offsets and padding but do not actually read/write
     * the real storage contents, only record the metadata in the archive.
     *
     * Then we know the size will be metadata only archive size + number of bytes
     * in the content for all the files.
     */
    public function getTarSize($filename,$opts) {

        // work out the content length
        $tfn = tempnam( Filesystem::getTempDirectory(), 'szf');
        $outstream = fopen($tfn,'w');
        // we send the headers with the main TarArchive not the
        // one that just calculates the content length.
        $opts['send_http_headers'] = false;
        $contentsz = 0;
        $archive = new \Barracuda\ArchiveStream\TarArchive($filename . ".tar",$opts,$filename,$outstream);
        
        // collect info for each file
        foreach ($this->files as $k => $data) {
            $file = $data['data'];
            $fileopts = array();
            $transfer = $file->transfer;
            if( (!isset($archivedName)) ) {
                $archivedName = $this->getArchivedFileName( $file );
            }

            $contentsz += $file->size;
	    $archive->init_file_stream_transfer($archivedName, $file->size, $fileopts);
	    $archive->complete_file_stream();        
            
        }

        $archive->finish();        

        fflush($outstream);
        $ret = $contentsz + filesize($tfn);
        fclose($outstream);
        unlink($tfn);
        
        return $ret;
    }
    
    /**
     * Creates an archive in the format set in the constructor 
     * The archive is created on-the-fly and streamed it to the client.
     *
     * <b>The files in the archive are not compressed.</b>
     */
    public function streamArchive( $recipient = null )
    {
        $fuid = substr(hash('sha1', implode('+', array_keys($this->files))), -8);
        $file = reset($this->files);
        $tid = $file['data']->transfer_id;
        $filename = 'transfer_' . $tid . '_files_' . $fuid;

        //
        // Generate tar or zip
        //
        if( $this->archive_format == 'tar' ) {
            
            $opts = array();
            // we send the headers with the main TarArchive not the
            // one that just calculates the content length.
            $opts['send_http_headers'] = false; 
            $opts['content_type'] = 'application/x-tar';
            
            // work out the content length
            $sz = $this->getTarSize($filename,$opts);
            header("Content-Length: $sz");
            $opts['send_http_headers'] = true;

            // do the work for real now and stream things over to the client
            $outstream = fopen('php://output','w');
            $archive = new \Barracuda\ArchiveStream\TarArchive($filename . ".tar",$opts,$filename,$outstream);

            // send each file
            foreach ($this->files as $k => $data) {
                $file = $data['data'];
                $transfer = $file->transfer;

                if ($recipient) {
                    Logger::logActivity(LogEventTypes::DOWNLOAD_STARTED, $file, $recipient);
                }
                
                $this->addFileToArchive( $archive, $file );
            }

            $archive->finish();        

        } else {

            $options = new ZipStream\Option\Archive();
            $options->setSendHttpHeaders(false);
            $options->setZeroHeader(true);
            $options->setDeflateLevel(0);
            
            $contentLength = $this->getZipSize( $filename, $options );
            header("Content-Length: $contentLength");
            
            $outstream = fopen('php://output','w');
            $options->setSendHttpHeaders(true);
            $options->setOutputStream($outstream);
            $archive = new ZipStream\ZipStream($filename . ".zip", $options);        
            
            // send each file
            foreach ($this->files as $k => $data) {
                $file = $data['data'];
                $transfer = $file->transfer;
                if ($recipient) {
                    Logger::logActivity(LogEventTypes::DOWNLOAD_STARTED, $file, $recipient);
                }

                $archivedName = $this->getArchivedFileName( $file );

                
                $fileopts = new ZipStream\Option\File();
                $fileopts->defaultTo($options);
                $fileopts->setMethod(ZipStream\Option\Method::STORE());
                $zipfile = new ZipStream\File($archive, $archivedName, $fileopts);
                
                $stream = $file->getStream();
                $zipfile->processStream(new ZipStream\Stream($stream));
                fclose($stream);
            }

            $archive->finish();        
        }

        if ($recipient) {
            foreach ($this->files as $data) {
                $file = $data['data'];
                Logger::logActivity(LogEventTypes::DOWNLOAD_ENDED, $file, $recipient);
            }
        }
        
        // ok
        return true;
    }

    // ------------------------------------------------------------------------
    // Utilities functions
    // ------------------------------------------------------------------------
    

    // ------------------------------------------------------------------------
    // private functions
    // ------------------------------------------------------------------------

    protected function getArchivedFileName( $file )
    {
        $name = preg_replace('/^\\/+/', '', $file->path); // Strip leading slashes from filename.
        $name = preg_replace('/\\.\\.\\//', '', $name);   // strip ../
        $name = preg_replace('/\\/\\.\\./', '', $name);   // strip /..
        return $name;
    }
    
    protected function addFileToArchive( $archive, $file, $archivedName = null )
    {
        $fileopts = array();
        $transfer = $file->transfer;
        if( (!isset($archivedName)) ) {
            $archivedName = $this->getArchivedFileName( $file );
        }
        
	$archive->init_file_stream_transfer($archivedName, $file->size, $fileopts);

        $block_size = Config::get('upload_chunk_size');
        $stream = $file->getStream();
	while ($data = fread($stream, $block_size))
	{
	    $archive->stream_file_part($data);
            if( feof($stream) ) {
                break;
            }            
	}
        fclose($stream);
	$archive->complete_file_stream();        
    }
    
}
