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

/* ---------------------------------
 * download using PHP from a non web accessible folder
 * ---------------------------------
 *
 */
require_once('../classes/_includes.php');

global $config;

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$functions = Functions::getInstance();
$saveLog = Log::getInstance();
$sendmail = Mail::getInstance();

date_default_timezone_set($config['Default_TimeZone']);

if(session_id() == ""){
	// start new session and mark it as valid because the system is a trusted source
	// set cache headers to 'private' to allow IE downloads
	session_cache_limiter('private_no_expire');
	session_start();
	$_SESSION['validSession'] = true;
}

// check we are authenticated (a valid download voucher id)
if(!$authvoucher->aVoucher()) {
	logEntry("Download: Failed authentication","E_ERROR");
	 header( 'Location: index.php?s=invalidvoucher' ) ;
} else { // Start authenticated clause
if (isset($_REQUEST["vid"])) {

// load the voucher
$fileArray =  $authvoucher->getVoucher();
$fileoriginalname = $fileArray[0]['fileoriginalname'];
$fileuid = $fileArray[0]['fileuid'];
$file=$config['site_filestore'].$fileuid.".tmp";
$filestatus = $fileArray[0]['filestatus'];

//$download_rate = 20000.5;

// check if file physically exists and is marked 'Available' before downloading
if(file_exists($file) && is_file($file) && $filestatus == 'Available')
{
	// Check the encoding for the filename and convert if necessary
	if (detect_char_encoding($fileoriginalname) == 'ISO-8859-1') {
		$fileoriginalname = iconv("UTF-8", "ISO-8859-1", $fileoriginalname);
	}

	$filesize = $functions->getFileSize($file);
	$downloadComplete=false;
	$offset = 0;
	$length = $filesize;
	if ( isset($_SERVER['HTTP_RANGE']) ) {
		// if the HTTP_RANGE header is set we're dealing with partial content

		$partialContent = true;

		// find the requested range
		// this might be too simplistic, apparently the client can request
		// multiple ranges, which can become pretty complex, so ignore it for now
		preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);

		if(isset($matches[1])) {
			$offset = intval($matches[1]);
		}
		if(isset($matches[2])) {
			$length = intval($matches[2]) - $offset;
		} else {
			$length = $filesize - $offset - 1;
		}
		logEntry('Partial download requested HTTP_RANGE: ' . $_SERVER['HTTP_RANGE'],"E_NOTICE");

	} else {
		$partialContent = false;
	        logEntry("Full download requested for ".$filesize." bytes.","E_NOTICE");
	}

	// set download file headers
	// header("Content-Type: application/force-download"); // Check if this is needed ?
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$fileoriginalname.'"');
	header('Accept-Ranges: bytes');

	// as files may be very large - stop it timing out
	set_time_limit(0);

	session_write_close();

	logEntry("Download: Start Downloading - ".$file,"E_NOTICE");
	if($partialContent) {
		header('HTTP/1.1 206 Partial Content');
		header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $filesize);
		logEntry('Partial download header Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $filesize,"E_NOTICE");
		header('Content-Length: '.($length+1));
		logEntry('Partial download header Content-Length: ' . ($length+1),"E_NOTICE");

		// use range reading
		$sentBytes=readfile_range($file, $offset, $length);
		logEntry('Partial download sent '.$sentBytes.' bytes.',"E_NOTICE");
		if ($offset + $sentBytes == $filesize){
			// Last part downloaded so assume completed
			$downloadComplete=true;
		}
	} else {
		header('Content-Length: '.$filesize);
		$sentBytes=readfile_chunked($file);
		logEntry('Full download sent '.$sentBytes.' bytes.',"E_NOTICE");
		if($sentBytes == $filesize){
			// complete file is downloaded
			$downloadComplete=true;
		}
	}
	if ($downloadComplete) {
		// Send completed email
		$tempEmail = $fileArray[0]["fileto"];
		$fileArray[0]["fileto"] = $fileArray[0]["filefrom"];
		$fileArray[0]["filefrom"] = $tempEmail;
		$saveLog->saveLog($fileArray[0],"Download","");
		if (isset($config['download_confirmation_to_downloader']) && ! $config['download_confirmation_to_downloader']) {
			$sendmail->sendEmail($fileArray[0],$config['filedownloadedemailbody'],"nocctodownloader");
		} else {
			$sendmail->sendEmail($fileArray[0],$config['filedownloadedemailbody']);
		}
		logEntry("Download complete: email sent - To: ".$fileArray[0]["fileto"]."  From: ".$fileArray[0]["filefrom"] . " [".$file."]","E_NOTICE");
	}
} else {
	print_r("file not found clause");
	// physical file was not found
	logEntry("Download: File Not Found - ".$file,"E_ERROR");
	// redirect to file is no longer available
	 header( 'Location: index.php?s=invalidvoucher' ) ;
}
} // End _REQUEST["vid"] clause
} // End authenticated clause

// function reads chunks for range download
// To prevent memory exhaustion with large ranges the requested range
// should be read and buffered in smaller chunks.
function readfile_range($filename, $offset, $length, $retbytes=true) {
	ob_start();
	$chunksize = min($length+1,1*(1024*1024)); // how many bytes per chunk
	$buffer = '';
	$cnt =0;
	$handle = fopen($filename, 'rb'); // open the file
	if ($handle === false) {
		return false;
	}

	fseek($handle, $offset);

	while (!feof($handle) && $chunksize > 0 ) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
		$cnt += strlen($buffer);
		if ($length + 1 - $cnt < $chunksize) {
			$chunksize = $length + 1 - $cnt ;
		}
	}

	$status = fclose($handle);

	if ($retbytes && $status) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}
	return $status;
}

// function read the chunks from the non web enabled folder
function readfile_chunked($filename,$retbytes=true) {

	ob_start();

	$chunksize = 1*(1024*1024); // how many bytes per chunk
	$buffer = '';
	$cnt =0;
	$handle = fopen($filename, 'rb'); // open the file
	if ($handle === false) {
		return false;
	}
	while (!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
		if ($retbytes) {
			$cnt += strlen($buffer);
		}
	}
	$status = fclose($handle);

	if ($retbytes && $status) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}
	return $status;
}

?>
