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
* *	Redistributions of source code must retain the above copyright
* 	notice, this list of conditions and the following disclaimer.
* *	Redistributions in binary form must reproduce the above copyright
* 	notice, this list of conditions and the following disclaimer in the
* 	documentation and/or other materials provided with the distribution.
* *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
* 	names of its contributors may be used to endorse or promote products
* 	derived from this software without specific prior written permission.
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
 * See (@link http://www.pkware.com/documents/casestudies/APPNOTE.TXT APPNOTE.TXT) for a full specification of the .ZIP file format.
 * Code inspired by Paul Duncan's (@link http://pablotron.org/software/zipstream-php/ ZipStream-PHP).
 */
class Zipper
{
    private $files;
    private $centralDirectory;

    public function __construct()
    {
        $this->files = array();
        $this->centralDirectory = array();
    }

    /**
     * Adds a file to be sent as part of the ZIP at a later time.
     *
     * @param array $file Must be a single file array retrieved from getVoucherData() or getMultiFileData().
     */
    public function addFile(array $file)
    {
        $this->files[] = $file;
    }

    /**
     * Creates a ZIP archive on-the-fly and streams it to the client.
     */
    public function sendZip()
    {
        global $functions, $config;

        $this->sendHttpHeaders();
        $offset = 0;

        foreach ($this->files as $file) {
            // Set up metadata and send the local header.
            $name = preg_replace('/^\\/+/', '', $file["fileoriginalname"]); // Strip leading slashes from filename.
            $timestamp = $functions->unixToDosTime(strtotime($file["filecreateddate"]));
            $localHeaderLength = $this->sendLocalFileHeader($name, $timestamp);

            // File may be too large to hash all at once, so create a hash context and update it as the file is read.
            $path = $config['site_filestore'] . $file['fileuid'] . '.tmp';
            $hashContext = hash_init("crc32b");
            $fileDataLength = $this->sendFileData($path, $hashContext);

            // Get the finished CRC32 hash of the file and send the descriptor.
            $crc32 = unpack('N', pack('H*', hash_final($hashContext)));
            $crc32 = $crc32[1];
            $descriptorLength = $this->sendFileDescriptor($file['filesize'], $crc32);

            // Store file information to put in the CDR section later.
            $this->centralDirectory[] = array(
                "name" => $name,
                "crc" => $crc32,
                "size" => $file['filesize'],
                "offset" => $offset,
                "timestamp" => $timestamp
            );

            $offset += $localHeaderLength + $fileDataLength + $descriptorLength;
        }

        $this->sendCentralDirectory($offset);
    }

    private function sendHttpHeaders()
    {
        global $config;

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $config['site_name'] . '-' . $this->files[0]['filetrackingcode'] . '.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $this->calculateTotalFileSize());
    }

    private function calculateTotalFileSize()
    {
        $fileSize = 22; // Size of the end-of-file central directory record.

        foreach ($this->files as $file) {
            $fileSize += 92 + strlen($file["fileoriginalname"]) * 2; // Size of the local file header, descriptor and per-file CDR entry.
            $fileSize += $file["filesize"]; // File data size.
        }

        return $fileSize;
    }

    private function sendLocalFileHeader($filename, $timestamp)
    {
        $nameLength = strlen($filename);

        $header = pack('V', 0x04034b50) // Local file header signature.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('v', 0x08) // General purpose bit flag (third bit is set to allow us to send the hash later, in the file descriptor).
            . pack('v', 0x00) // Compression method (0x00 means no compression).
            . pack('V', $timestamp) // Last modified time and date (as a DOS timestamp).
            . pack('V', 0x00) // CRC-32 hash of file contents. 0x00 because it is specified in the file descriptor.
            . pack('V', 0x00) // Compressed data length. 0x00 because it is specified in the file descriptor.
            . pack('V', 0x00) // Uncompressed data length. 0x00 because it is specified in the file descriptor.
            . pack('v', $nameLength) // Length of the file name.
            . pack('v', 0); // Length of the extra data field.

        echo $header . $filename;

        return strlen($header) + $nameLength; // Return the byte size of the header.
    }

    private function sendFileData($path, $hashContext)
    {
        global $config;
        set_time_limit(0); // Prevent the download from timing out.

        $bytesSent = 0;
        $handle = fopen($path, 'rb');
        $chunkSize = $config['download_chunk_size'];

        // Read the file into memory in small chunks, send the chunks to the client and update the CRC-32 hash.
        while ($data = fread($handle, $chunkSize)) {
            echo $data;
            hash_update($hashContext, $data);
            $bytesSent += strlen($data);
        }

        fclose($handle);

        return $bytesSent;
    }

    private function sendFileDescriptor($fileSize, $crc32)
    {
        // Send the file descriptor that goes at the end of each file entry.
        $descriptor = pack('V', 0x08074b50)
            . pack('V', $crc32)
            . pack('V', $fileSize)
            . pack('V', $fileSize);

        echo $descriptor;

        return strlen($descriptor);
    }

    private function sendCentralDirectory($offset)
    {
        $cdrOffset = 0;

        // Send the CDR for each file.
        foreach ($this->centralDirectory as $file) {
            $cdrOffset += $this->sendFileCDR($file);
        }

        // Send the final end-of-file central directory record.
        $this->sendFinalCDR($cdrOffset, $offset);
    }

    private function sendFileCDR($file)
    {
        // Send the central directory record belonging to a file.
        $record = pack('V', 0x02014b50) // Central file header signature.
            . pack('v', 0) // Made by version.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('v', 0x08) // General purpose bit flag.
            . pack('v', 0x00) // Compression method (0x00 means no compression).
            . pack('V', $file["timestamp"]) // Last modified time and date (as a DOS timestamp).
            . pack('V', $file["crc"]) // CRC-32 hash of file contents.
            . pack('V', $file["size"]) // Compressed data length. Equal to file size because of no compression.
            . pack('V', $file["size"]) // Uncompressed data length.
            . pack('v', strlen($file["name"])) // Length of the file name.
            . pack('v', 0) // Length of the extra data field.
            . pack('v', 0) // Length of the commend field.
            . pack('v', 0) // Disk number start.
            . pack('v', 0) // Internal file attributes.
            . pack('V', 32) // External file attributes.
            . pack('V', $file["offset"]) // Relative offset of local header.
            . $file["name"];

        echo $record;
        return strlen($record);
    }

    private function sendFinalCDR($cdrOffset, $offset)
    {
        $num = count($this->centralDirectory);
        $cdrLength = $cdrOffset;
        $cdrOffset = $offset;

        // Send the final central directory section which goes at the end of the ZIP archive.
        $record = pack('V', 0x06054b50) // End of central directory record signature.
            . pack('v', 0x00) // Number of this disk.
            . pack('v', 0x00) // Number of the disk with the start of the central directory.
            . pack('v', $num) // Number of CDR entries on this disk.
            . pack('v', $num) // Total number of CDR entries.
            . pack('V', $cdrLength) // Size of the central directory.
            . pack('V', $cdrOffset) // Size of CDR offset with respect to starting disk number.
            . pack('v', 0); // Length of the file comment.

        echo $record;
    }
}
