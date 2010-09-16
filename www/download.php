<?php

/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once('../classes/_includes.php');

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();
$CFG = config::getInstance();
$config = $CFG->loadConfig();
$functions = Functions::getInstance();
$saveLog = Log::getInstance();
$sendmail = Mail::getInstance();

if(!$authvoucher->aVoucher() && !$authsaml->isAuth()) {
		echo "notAuthenticated";
} else {
if (isset($_REQUEST["vid"])) {

// load the voucher
$fileArray =  json_decode($authvoucher->getVoucher(), true);
$fileoriginalname = $fileArray[0]['fileoriginalname'];
$fileuid = $fileArray[0]['fileuid'];	
$file=$config['site_filestore'].$fileuid.$fileoriginalname;

//$download_rate = 20000.5;
if(file_exists($file) && is_file($file))
{

	header("Content-Type: application/force-download");
	header('Content-Type: application/octet-stream');
    header('Content-Length: '.getFileSize($file));
	header('Content-Disposition: attachment; filename='.$fileoriginalname);
	set_time_limit(0);
	readfile_chunked($file);
	// email completed
		$tempEmail = $fileArray[0]["fileto"];
		$fileArray[0]["fileto"] = $fileArray[0]["filefrom"];	
		$fileArray[0]["filefrom"] = $tempEmail;
		$saveLog->saveLog($fileArray[0],"Download","");
		$sendmail->sendEmail($fileArray[0],$config['filedownloadedemailbody']);
}
else 
{
    die('Error: The file '.$file.' does not exist!');
}
}
}

function readfile_chunked($filename,$retbytes=true) {

$chunksize = 1*(1024*1024); // how many bytes per chunk
   $buffer = '';
   $cnt =0;
   $handle = fopen($filename, 'rb');
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

function getFileSize($filename){

global $config;
		
	if($filename == "" ) {
		return;
	} else {
		$file = $filename;//$config["site_filestore"].sanitizeFilename($filename);
	
		if (file_exists($file)) {
			if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
			{
				$size = trim(shell_exec("stat -c%s ". escapeshellarg($file)));
			} else { 
			   	$fsobj = new COM("Scripting.FileSystemObject"); 
				$f = $fsobj->GetFile($file); 
				$size = $f->Size; 
			}
				return $size;
			} else { 
				return 0;
			} 
		}
	}
	
 ?>
