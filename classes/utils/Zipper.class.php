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
     * Use 64 bits for archive
     */
    private $useZip64;
    
    /**
     * Constuctor of Zipper class
     */
    public function __construct(){
        $this->files = array();
        $this->useZip64 = false;
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
    public function sendZip($withHeaders = true){
        // Note: Mac OS X has compatibility issues with ZIP64 files, so only enable the ZIP64 format if
        // we have to (i.e. if the total file size is 4 GiB or greater).
        $this->useZip64 = $this->calculateTotalFileSize() >= 4 * 1024 * 1024 * 1024;

        if ($withHeaders){
            $this->sendHttpHeaders();
        }
        
        $offset = 0;

        foreach ($this->files as $k => $data) {
            $file = $data['data'];
            
            Logger::logActivity(LogEventTypes::DOWNLOAD_START, $file);
            
            $transfer = $file->transfer;

            // Set up metadata and send the local header.
            $name = preg_replace('/^\\/+/', '', $file->name); // Strip leading slashes from filename.
            
            //timestamps
            $timestamp = $this->unixToDosTime(strtotime($transfer->created));
            
            $localHeaderLength = $this->sendLocalFileHeader($name, $timestamp, $file->size);

            // File may be too large to hash all at once, 
            // so create a hash context and update it as the file is read.
            $hashContext = hash_init('crc32b');

            // Send the file contents.
            $fileDataLength = $this->sendFileData($file, $hashContext);

            // Get the finished CRC32 hash of the file and send it in the end-of-file data descriptor.
            $crc32 = unpack('N', pack('H*', hash_final($hashContext)));
            $crc32 = $crc32[1];
            $descriptorLength = $this->sendFileDescriptor($file->size, $crc32);

            // Store file information to put in the CDR section later.
            $this->files[$file->id]['content']['crc'] = $crc32;
            $this->files[$file->id]['content']['offset'] = $offset;
            $this->files[$file->id]['content']['timestamp'] = $timestamp;

            // Add the size of this file entry to the offset (needed for the TOC, Table Of Content).
            $offset += $localHeaderLength + $fileDataLength + $descriptorLength;
        }

        // Write the TOC at the end of the ZIP archive.
        $bytesSent = $this->sendTableOfContent($offset) + $offset;

        if ($bytesSent == $this->calculateTotalFileSize()) {
            // Download was completed, save a log entry for each of the downloaded files.
            foreach ($this->files as $data) {
                $file = $data['data'];
                Logger::logActivity(LogEventTypes::DOWNLOAD_END, $file);
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * Creates the ZIP file header for an individual file in the archive (sent before the file contents).
     * 
     * 
     * @param String $filename: the file name
     * @param (DOS) timestamp $timestamp: the timestamp (DOS format)
     * @param int $fileSize: the file size
     * 
     * @return int: byte size of the header.
     */
    private function sendLocalFileHeader($filename, $timestamp, $fileSize){
        $nameLength = strlen($filename);

        // If using ZIP64, add an extra 20-byte field at the end of the header with 64-bit file sizes.
        $extraFieldLength = $this->useZip64 ? 20 : 0;

        // 0xFFFFFFFF indicates that the file size will be specified in the extra field.
        $dataLength = $this->useZip64 ? 0xFFFFFFFF : 0x00;

        $header = pack('V', 0x04034b50) // Local file header signature.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('v', 0x08 | 0x0800) // General purpose bit flag: set bit 3 (file descriptor) and bit 11 (utf-8 file names).
            . pack('v', 0x00) // Compression method (0x00 means no compression).
            . pack('V', $timestamp) // Last modified time and date (as a DOS timestamp).
            . pack('V', 0x00) // CRC-32 hash of file contents. 0x00 because it is specified in the file descriptor.
            . pack('V', $dataLength) // Compressed data length. 0x00 because it is specified in the file descriptor.
            . pack('V', $dataLength) // Uncompressed data length. 0x00 because it is specified in the file descriptor.
            . pack('v', $nameLength) // Length of the file name.
            . pack('v', $extraFieldLength) // Length of the extra data field.
            . $filename;

        if ($this->useZip64) {
            // Add ZIP64 extended information.
            $header .= pack('v', 0x0001) // Identifier for the ZIP64 extra data field.
                . pack('v', 16) // Size of the rest of this field.
                . $this->pack64($fileSize) // Compressed data length as a 64-bit field.
                . $this->pack64($fileSize); // Uncompressed data length as a 64-bit field.
        }

        echo $header;

        return strlen($header); // Return the byte size of the header.
    }

    /**
     * Send the contents of a file in the archive. 
     * The file is read and sent in small chunks to avoid
     * reading very large files into memory. 
     * Updates the hash context along the way.
     * 
     * @param String $path: path to the file
     * @param String $hashContext: the hash context
     * 
     * @return int: the bytes sent
     */
    private function sendFileData($file, $hashContext){
        
        $bytesSent = 0;
        $chunkSize = Config::get('download_chunk_size');
        
        for ($offset=0 ; $offset <= $file->size; $offset+=$chunkSize){
            $data = $file->readChunk($offset);
            echo $data;
            hash_update($hashContext, $data);
            $bytesSent += strlen($data);
        }
        return $bytesSent;
    }

    /**
     * Sends the file descriptor that goes at the end of each file entry. 
     */
    private function sendFileDescriptor($fileSize, $crc32){
        $packedFileSize = $this->useZip64 ? $this->pack64($fileSize) : pack('V', $fileSize);

        $descriptor = pack('V', 0x08074b50) // Signature for the file descriptor field.
            . pack('V', $crc32) // Completed CRC32 hash of the file.
            . $packedFileSize // Compressed file size.
            . $packedFileSize; // Uncompressed file size.

        echo $descriptor;

        return strlen($descriptor);
    }

    /**
     * Sends the "TOC" (table oc content) section which goes at the end of the ZIP archive.
     * 
     * @param int $offset
     * 
     * @return type
     */
    private function sendTableOfContent($offset){
        $cdrOffset = 0;

        foreach ($this->files as $k => $data) {
            // Send a TOC record for each file.
            $cdrOffset += $this->sendFileTOC($data);
        }

        // Send the final end-of-file TOC record and return # bytes sent.
        $bytesSent = 0;

        if ($this->useZip64) {
            $bytesSent = $this->sendZip64TOC($cdrOffset, $offset);
        }

        return $bytesSent + $this->sendFinalTOC($cdrOffset, $offset) + $cdrOffset;
    }

    /**
     * Takes an integer and returns it as a 64-bit unsigned little-endian binary string.
     * 
     * @param int $value: value to ve converted
     * 
     * @return int: 64-bit unsigned value
     */
    private function pack64($value){
        $left = ($value & 0xFFFFFFFF00000000) >> 32;
        $right = $value & 0x00000000FFFFFFFF;

        return pack('VV', $right, $left);
    }
    
    /**
     * Sends a TOC (Table Of Content) record for a single file in the archive.
     * 
     * @param File $file: the file
     * 
     * @return int: length of the record
     */
    private function sendFileTOC($file){
        $fileData = $file['data'];
        $fileContent = $file['content'];
        
        $fileSize = $this->useZip64 ? 0xFFFFFFFF : $fileData->size;
        $extraFieldLength = $this->useZip64 ? 20 : 0;

        // Send the TOC record belonging to a file.
        $record = pack('V', 0x02014b50) // Central file header signature.
            . pack('v', 0) // Made by version.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('v', 0x08 | 0x0800) // General purpose bit flag: set bit 3 (file descriptor) and bit 11 (utf-8 file names).
            . pack('v', 0x00) // Compression method (0x00 means no compression).
            . pack('V', $fileContent['timestamp']) // Last modified time and date (as a DOS timestamp).
            . pack('V', $fileContent['crc']) // CRC-32 hash of file contents.
            . pack('V', $fileSize) // Compressed data length. Equal to file size because of no compression.
            . pack('V', $fileSize) // Uncompressed data length.
            . pack('v', strlen($fileData->name)) // Length of the file name.
            . pack('v', $extraFieldLength) // Length of the extra data field.
            . pack('v', 0) // Length of the commend field.
            . pack('v', 0) // Disk number start.
            . pack('v', 0) // Internal file attributes.
            . pack('V', 32) // External file attributes.
            . pack('V', $fileContent['offset']) // Relative offset of local header.
            . $fileData->name;


        if ($this->useZip64) {
            $record .= pack('v', 0x0001) // Zip64 extra block signature.
                . pack('v', 16) // Size of the following extra block.
                . $this->pack64($fileData->size) // Uncompressed file size.
                . $this->pack64($fileData->size); // Compressed file size.
        }

        echo $record;
        return strlen($record);
    }

    /**
     * Sends additional end-of-archive ZIP64 information.
     * 
     * @param int $cdrOffset
     * @param int $offset
     * 
     * @return int: length of the record
     */
    private function sendZip64TOC($cdrOffset, $offset){
        $numEntries = count($this->files);
        $cdrLength = $cdrOffset;
        $cdrOffset = $offset;

        $record = pack('V', 0x06064b50) // ZIP64 end of TOC signature.
            . $this->pack64(44) // Size of the rest of this record.
            . pack('v', 0) // Made by version.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('V', 0x0000) // Number of this disk.
            . pack('V', 0x0000) // Number of the disk with the start of the TOC.
            . $this->pack64($numEntries) // Number of CDR entries on this disk.
            . $this->pack64($numEntries) // Total number of CDR entries.
            . $this->pack64($cdrLength) // Size of TOC.
            . $this->pack64($cdrOffset); // Start of TOC.

        $record .= pack('V', 0x07064b50) // ZIP64 end of TOC locator signature.
            . pack('V', 0x0000) // Number of the disk with the start of the Zip64 CDR.
            . $this->pack64($cdrLength + $cdrOffset) // Offset of start of Zip64 CDR.
            . pack('V', 0x0001); // Number of disks.

        echo $record;

        return strlen($record);
    }
    
    /**
     * Send the final end-of-archive TOC (Table Of Content - contains meta data for ZIP archive).
     * 
     * @param type $cdrOffset
     * @param type $offset
     * 
     * @return int: length of the record
     */
    private function sendFinalTOC($cdrOffset, $offset){
        $num = count($this->files);
        $cdrLength = $cdrOffset;
        $cdrOffset = $offset;

        if ($this->useZip64) {
            $cdrOffset = 0xFFFFFFFF;
        }

            // Send the final TOC section which goes at the end of the ZIP archive.
        $record = pack('V', 0x06054b50) // End of TOC record signature.
            . pack('v', 0x00) // Number of this disk.
            . pack('v', 0x00) // Number of the disk with the start of the TOC.
            . pack('v', $num) // Number of CDR entries on this disk.
            . pack('v', $num) // Total number of CDR entries.
            . pack('V', $cdrLength) // Size of the TOC.
            . pack('V', $cdrOffset) // Size of CDR offset with respect to starting disk number.
            . pack('v', 0); // Length of the file comment.

        echo $record;
        return strlen($record);
    }
    
    
    // ------------------------------------------------------------------------
    // Utilities functions
    // ------------------------------------------------------------------------
    
    
    /**
     * Returns the total file size of the ZIP archive, depending on the number and sizes of the files and whether
     * or not the ZIP64 format is being used.
     * 
     * @return int: the total filesize of the archive
     */
    private function calculateTotalFileSize(){
        $fileSize = 22; // Size of the end-of-file TOC record.

        foreach ($this->files as $data) {
            $file = $data['data'];
            $fileSize += 92 + strlen($file->name) * 2; // Size of the local file header, descriptor and per-file CDR entry.
            $fileSize += $file->size; // File contents size.

            if ($this->useZip64) {
                $fileSize += 48; // Extra file data for ZIP64 format.
            }
        }

        if ($this->useZip64) {
            $fileSize += 76; // Extra end-of-file ZIP64 information.
        }

        return $fileSize;
    }
    
    /**
     * Send the HTTP headers necessary to inform the browser of an incoming download.
     */
    private function sendHttpHeaders(){
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . Config::get('site_name') . '-files.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $this->calculateTotalFileSize());
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    }
    
    /**
     * Converts from UNIX to DOS style timestamp.
     * Defaults to current time if $timestamp parameter is missing or 0.
     * 
     * @param int $timestamp: timestamp to convert
     * @return timestamp converted
     */
    public function unixToDosTime($timestamp = 0){
        $timeBit = ($timestamp == 0) ? getdate() : getdate($timestamp);

        if ($timeBit['year'] < 1980) {
            return (1 << 21 | 1 << 16);
        }

        $timeBit['year'] -= 1980;

        return (
            $timeBit['year'] << 25 | $timeBit['mon'] << 21 |
            $timeBit['mday'] << 16 | $timeBit['hours'] << 11 |
            $timeBit['minutes'] << 5 | $timeBit['seconds'] >> 1
        );
    }
}

