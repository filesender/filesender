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

/*
 * ---------------------------------
 * Download multiple files at once as an uncompressed ZIP archive.
 * See http://www.pkware.com/documents/casestudies/APPNOTE.TXT for a full specification of the .ZIP file format.
 * Code inspired by Paul Duncan's ZipStream-PHP: http://pablotron.org/software/zipstream-php/
 * ---------------------------------
 */
require_once('../classes/_includes.php');

global $config;

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$functions = Functions::getInstance();
$saveLog = Log::getInstance();
$sendmail = Mail::getInstance();

date_default_timezone_set($config['Default_TimeZone']);

if (session_id() == "") {
    // Start new session and mark it as valid because the system is a trusted source.
    // Set cache headers to 'private' to allow IE downloads.
    session_cache_limiter('private_no_expire');
    session_start();
    $_SESSION['validSession'] = true;
}

if (isset($_REQUEST["gid"])) {
    $fileArray = $functions->getMultiFileData($_REQUEST["gid"]);

    redirectIfSingleDownload();

    sendHttpHeaders($fileArray);

    $centralDirectoryRecord = array();
    $offset = 0;

    foreach ($fileArray as $file) {
        if (!shouldDownloadFile($file)) continue; // File is not selected for download, skip it.

        $path = $config['site_filestore'] . $file['fileuid'] . '.tmp';
        $timestamp = unixToDosTime(strtotime($file["filecreateddate"]));

        // Strip leading slashes from filename.
        $name = preg_replace('/^\\/+/', '', $file["fileoriginalname"]);

        $localHeaderLength = sendLocalFileHeader($name, $timestamp);

        // File may be too large to hash all at once, so create a hash context and update it as the file is read.
        $hashContext = hash_init("crc32b");
        $fileDataLength = sendFileData($path, $hashContext);

        // Get the finished CRC32 hash of the file.
        $array = unpack('N', pack('H*', hash_final($hashContext)));
        $crc32 = $array[1];

        $descriptorLength = sendFileDescriptor($file['filesize'], $crc32);

        // Store file information to put in the CDR section later.
        $centralDirectoryRecord[] = array(
            "name" => $name,
            "crc" => $crc32,
            "size" => $file['filesize'],
            "offset" => $offset,
            "timestamp" => $timestamp
        );

        $offset += $localHeaderLength + $fileDataLength + $descriptorLength;
    }

    $cdrOffset = 0;

    foreach ($centralDirectoryRecord as $file) {
        $cdrOffset += sendFileCDR($file);
    }

    // Final central directory record.
    sendFinalCDR($centralDirectoryRecord, $cdrOffset, $offset);
}

function sendHttpHeaders($fileArray)
{
    global $config;
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $config['site_name'] . '-' . $fileArray[0]['filetrackingcode'] . '.zip"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . calculateTotalFileSize($fileArray));
}

// Sends the header that goes before each file in the zip. Returns the byte size of the sent header.
function sendLocalFileHeader($filename, $timestamp)
{
    $nameLength = strlen($filename);

    $header = pack('V', 0x04034b50) // Local file header signature.
        . pack('v', 45) // Version needed to extract.
        . pack('v', 0x08) // General purpose bit flag (third bit is set to allow us to send the hash later, in the file descriptor).
        . pack('v', 0x00) // Compression method (0x00 means no compression).
        . pack('V', $timestamp) // Last modified time and date (as a DOS timestamp).
        . pack('V', 0x00) // CRC-32 hash of file contents. 0x00 because it is specified in the file descriptor.
        . pack('V', 0x00) // Compressed data length. 0x00 because it is specified in the file descriptor.
        . pack('V', 0x00) // Uncompressed data length. 0x00 because it is specified in the file descriptor.
        . pack('v', $nameLength) // Length of the file name.
        . pack('v', 0); // Length of the extra data field.

    echo $header . $filename;

    return strlen($header) + $nameLength;
}

// Reads the file into memory in chunks, sends the chunks to the client and updates the hash. Returns number of bytes sent.
function sendFileData($path, $hashContext)
{
    global $config;
    set_time_limit(0); // Prevent the download from timing out.

    $bytesSent = 0;
    $handle = fopen($path, 'rb');

    while ($data = fread($handle, $config["download_chunk_size"])) {
        echo $data;
        hash_update($hashContext, $data);
        $bytesSent += strlen($data);
    }

    fclose($handle);

    return $bytesSent;
}

// Sends the file descriptor that goes at the end of each file entry in the zip. Returns the byte size of the descriptor.
function sendFileDescriptor($fileSize, $crc32)
{
    $descriptor = pack('V', $crc32)
        . pack('V', $fileSize)
        . pack('V', $fileSize);

    echo $descriptor;

    return strlen($descriptor);
}

// Sends the central file header for a file. Returns the byte size of the record.
function sendFileCDR($file)
{
    $record = pack('V', 0x02014b50) // Central file header signature.
        . pack('v', 0) // Made by version.
        . pack('v', 45) // Version needed to extract.
        . pack('v', 0x00) // General purpose bit flag.
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

// Sends the final central directory record which goes at the end of the ZIP.
function sendFinalCDR($centralDirectoryRecord, $cdrOffset, $offset)
{
    $num = count($centralDirectoryRecord);
    $cdrLength = $cdrOffset;
    $cdrOffset = $offset;

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

// Returns the size of the resulting zip file in bytes.
function calculateTotalFileSize($fileArray)
{
    $fileSize = 22; // Size of the end-of-file central directory record.

    foreach ($fileArray as $file) {
        if (!shouldDownloadFile($file)) continue;

        $fileSize += 88 + strlen($file["fileoriginalname"]) * 2; // Size of the local file header, descriptor and per-file CDR entry.
        $fileSize += $file["filesize"]; // File data size.
    }

    return $fileSize;
}

// If only one file was selected, redirect to the single-file download page. The $_REQUEST array contains the gid followed by
// the file voucher IDs and an "isformrequest" flag. Fetch the second key from the array to get the vid of the single file.
function redirectIfSingleDownload() {
    if (count($_REQUEST) == 3) {
        next($_REQUEST);
        header('Location: download.php?vid=' . key($_REQUEST));
    }
}

// Returns true if the file is selected for download, false otherwise.
function shouldDownloadFile($file) {
    return !isset($_REQUEST["isformrequest"]) || isset($_REQUEST[$file["filevoucheruid"]]);
}

// Converts from UNIX to DOS style timestamp, for use in .ZIP "Last modified time/date" fields.
function unixToDosTime($_timestamp = 0)
{
    $timeBit = ($_timestamp == 0) ? getdate() : getdate($_timestamp);

    if ($timeBit['year'] < 1980) {
        return (1 << 21 | 1 << 16);
    }

    $timeBit['year'] -= 1980;

    return ($timeBit['year'] << 25 | $timeBit['mon'] << 21 |
        $timeBit['mday'] << 16 | $timeBit['hours'] << 11 |
        $timeBit['minutes'] << 5 | $timeBit['seconds'] >> 1);
}
