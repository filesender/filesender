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


// --------------------------------
// Stream multiple files at once as an uncompressed ZIP archive. The archive is created on-the-fly and does not require
// large files to be loaded into memory all at once.
//
// See http://www.pkware.com/documents/casestudies/APPNOTE.TXT for a full specification of the .ZIP file format.
// Code inspired by Paul Duncan's (@link http://pablotron.org/software/zipstream-php/ ZipStream-PHP).
// --------------------------------
class Zipper
{
    private $files;
    private $centralDirectory;
    private $useZip64;
    private $sendDownloadComplete = false;

    public function __construct()
    {
        $this->files = array();
        $this->centralDirectory = array();
        $this->useZip64 = false;
    }

    // --------------------------------
    // Adds a file to be sent as part of the ZIP. Nothing is sent at this stage.
    // $file must be a single file array retrieved from getVoucherData() or getMultiFileData().
    // --------------------------------
    public function addFile(array $file)
    {
        $this->files[] = $file;
    }

    // --------------------------------
    // Creates a ZIP archive on-the-fly and streams it to the client. The files in the archive are not compressed.
    // --------------------------------
    public function sendZip()
    {
        global $functions;
        global $config;

        // Note: Mac OS X has compatibility issues with ZIP64 files, so only enable the ZIP64 format if
        // we have to (i.e. if the total file size is 4 GiB or greater).
        $this->useZip64 = $this->calculateTotalFileSize() >= 4 * 1024 * 1024 * 1024;

        $this->sendHttpHeaders();
        $offset = 0;

        foreach ($this->files as $file) {
            // Set up metadata and send the local header.
            $name = preg_replace('/^\\/+/', '', $file['fileoriginalname']); // Strip leading slashes from filename.
            $timestamp = $functions->unixToDosTime(strtotime($file['filecreateddate']));
            $localHeaderLength = $this->sendLocalFileHeader($name, $timestamp, $file['filesize']);

            // File may be too large to hash all at once, so create a hash context and update it as the file is read.
            $path = Config::get('site_filestore') . $file['fileuid'] . '.tmp';
            $hashContext = hash_init('crc32b');

            // Send the file contents.
            $fileDataLength = $this->sendFileData($path, $hashContext);

            // Get the finished CRC32 hash of the file and send it in the end-of-file data descriptor.
            $crc32 = unpack('N', pack('H*', hash_final($hashContext)));
            $crc32 = $crc32[1];
            $descriptorLength = $this->sendFileDescriptor($file['filesize'], $crc32);

            // Store file information to put in the CDR section later.
            $this->centralDirectory[] = array(
                'name' => $name,
                'crc' => $crc32,
                'size' => $file['filesize'],
                'offset' => $offset,
                'timestamp' => $timestamp
            );

            // Add the size of this file entry to the offset (needed for the central directory).
            $offset += $localHeaderLength + $fileDataLength + $descriptorLength;
        }

        // Write the central directory at the end of the ZIP archive.
        $bytesSent = $this->sendCentralDirectory($offset) + $offset;

        if ($bytesSent == $this->calculateTotalFileSize()) {
            // Download was completed, save a log entry for each of the downloaded files.
            $log = Log::getInstance();
            $sendMail = Mail::getInstance();
            $voucherIds = array();

            foreach ($this->files as $file) {
                $functions->incrementDownloadCount($file['filevoucheruid']); // Update DB download count.
                $log->saveLog($file, 'Download', '');
                $voucherIds[] = $file['filevoucheruid'];
            }

            // Send notification email to uploader.
            $sendMail->sendDownloadNotification($voucherIds, $this->sendDownloadComplete);
        }
    }

    // --------------------------------
    // Returns the total file size of the ZIP archive, depending on the number and sizes of the files and whether
    // or not the ZIP64 format is being used.
    // --------------------------------
    private function calculateTotalFileSize()
    {
        $fileSize = 22; // Size of the end-of-file central directory record.

        foreach ($this->files as $file) {
            $fileSize += 92 + strlen($file['fileoriginalname']) * 2; // Size of the local file header, descriptor and per-file CDR entry.
            $fileSize += $file['filesize']; // File contents size.

            if ($this->useZip64) {
                $fileSize += 48; // Extra file data for ZIP64 format.
            }
        }

        if ($this->useZip64) {
            $fileSize += 76; // Extra end-of-file ZIP64 information.
        }

        return $fileSize;
    }

    // --------------------------------
    // Send the HTTP headers necessary to inform the browser of an incoming download.
    // --------------------------------
    private function sendHttpHeaders()
    {
        global $config;

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . Config::get('site_name') . '-' . $this->files[0]['filetrackingcode'] . '.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $this->calculateTotalFileSize());
    }

    // --------------------------------
    // Creates the ZIP file header for an individual file in the archive (sent before the file contents).
    // --------------------------------
    private function sendLocalFileHeader($filename, $timestamp, $fileSize)
    {
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

    // --------------------------------
    // Takes an integer and returns it as a 64-bit unsigned little-endian binary string.
    // --------------------------------
    private function pack64($value)
    {
        $left = ($value & 0xFFFFFFFF00000000) >> 32;
        $right = $value & 0x00000000FFFFFFFF;

        return pack('VV', $right, $left);
    }

    // --------------------------------
    // Send the contents of a file in the archive. The file is read and sent in small chunks to avoid
    // reading very large files into memory. Updates the hash context along the way.
    // --------------------------------
    private function sendFileData($path, $hashContext)
    {
        global $config;
        set_time_limit(0); // Needed to prevent the download from timing out.

        $bytesSent = 0;
        $handle = fopen($path, 'rb');
        $chunkSize = Config::get('download_chunk_size');

        // Read the file into memory in small chunks, send the chunks to the client and update the CRC-32 hash.
        while ($data = fread($handle, $chunkSize)) {
            echo $data;
            hash_update($hashContext, $data);
            $bytesSent += strlen($data);
        }

        fclose($handle);

        return $bytesSent;
    }

    // --------------------------------
    // Sends the file descriptor that goes at the end of each file entry.
    // --------------------------------
    private function sendFileDescriptor($fileSize, $crc32)
    {
        $packedFileSize = $this->useZip64 ? $this->pack64($fileSize) : pack('V', $fileSize);

        $descriptor = pack('V', 0x08074b50) // Signature for the file descriptor field.
            . pack('V', $crc32) // Completed CRC32 hash of the file.
            . $packedFileSize // Compressed file size.
            . $packedFileSize; // Uncompressed file size.

        echo $descriptor;

        return strlen($descriptor);
    }

    // --------------------------------
    // Sends the "central directory" section which goes at the end of the ZIP archive.
    // --------------------------------
    private function sendCentralDirectory($offset)
    {
        $cdrOffset = 0;

        foreach ($this->centralDirectory as $file) {
            // Send a central directory record for each file.
            $cdrOffset += $this->sendFileCDR($file);
        }

        // Send the final end-of-file central directory record and return # bytes sent.
        $bytesSent = 0;

        if ($this->useZip64) {
            $bytesSent = $this->sendZip64CDR($cdrOffset, $offset);
        }

        return $bytesSent + $this->sendFinalCDR($cdrOffset, $offset) + $cdrOffset;
    }

    // --------------------------------
    // Sends a central directory record for a single file in the archive.
    // --------------------------------
    private function sendFileCDR($file)
    {
        $fileSize = $this->useZip64 ? 0xFFFFFFFF : $file['size'];
        $extraFieldLength = $this->useZip64 ? 20 : 0;

        // Send the central directory record belonging to a file.
        $record = pack('V', 0x02014b50) // Central file header signature.
            . pack('v', 0) // Made by version.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('v', 0x08 | 0x0800) // General purpose bit flag: set bit 3 (file descriptor) and bit 11 (utf-8 file names).
            . pack('v', 0x00) // Compression method (0x00 means no compression).
            . pack('V', $file['timestamp']) // Last modified time and date (as a DOS timestamp).
            . pack('V', $file['crc']) // CRC-32 hash of file contents.
            . pack('V', $fileSize) // Compressed data length. Equal to file size because of no compression.
            . pack('V', $fileSize) // Uncompressed data length.
            . pack('v', strlen($file['name'])) // Length of the file name.
            . pack('v', $extraFieldLength) // Length of the extra data field.
            . pack('v', 0) // Length of the commend field.
            . pack('v', 0) // Disk number start.
            . pack('v', 0) // Internal file attributes.
            . pack('V', 32) // External file attributes.
            . pack('V', $file['offset']) // Relative offset of local header.
            . $file['name'];


        if ($this->useZip64) {
            $record .= pack('v', 0x0001) // Zip64 extra block signature.
                . pack('v', 16) // Size of the following extra block.
                . $this->pack64($file['size']) // Uncompressed file size.
                . $this->pack64($file['size']); // Compressed file size.
        }

        echo $record;
        return strlen($record);
    }

    // --------------------------------
    // Sends additional end-of-archive ZIP64 information.
    // --------------------------------
    private function sendZip64CDR($cdrOffset, $offset)
    {
        $numEntries = count($this->centralDirectory);
        $cdrLength = $cdrOffset;
        $cdrOffset = $offset;

        $record = pack('V', 0x06064b50) // ZIP64 end of central directory signature.
            . $this->pack64(44) // Size of the rest of this record.
            . pack('v', 0) // Made by version.
            . pack('v', 0x000A) // Version needed to extract.
            . pack('V', 0x0000) // Number of this disk.
            . pack('V', 0x0000) // Number of the disk with the start of the central directory.
            . $this->pack64($numEntries) // Number of CDR entries on this disk.
            . $this->pack64($numEntries) // Total number of CDR entries.
            . $this->pack64($cdrLength) // Size of central directory.
            . $this->pack64($cdrOffset); // Start of central directory.

        $record .= pack('V', 0x07064b50) // ZIP64 end of central directory locator signature.
            . pack('V', 0x0000) // Number of the disk with the start of the Zip64 CDR.
            . $this->pack64($cdrLength + $cdrOffset) // Offset of start of Zip64 CDR.
            . pack('V', 0x0001); // Number of disks.

        echo $record;

        return strlen($record);
    }

    // --------------------------------
    // Send the final end-of-archive central directory (contains meta data for ZIP archive).
    // --------------------------------
    private function sendFinalCDR($cdrOffset, $offset)
    {
        $num = count($this->centralDirectory);
        $cdrLength = $cdrOffset;
        $cdrOffset = $offset;

        if ($this->useZip64) {
            $cdrOffset = 0xFFFFFFFF;
        }

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
        return strlen($record);
    }

    // --------------------------------
    // Sets the variable that sends out download complete receipt emails on.
    // --------------------------------
    public function enableDownloadCompleteEmail()
    {
        $this->sendDownloadComplete = true;
    }
}

