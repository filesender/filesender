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


if(!$authvoucher->aVoucher() && !$authsaml->isAuth()) {
		echo "notAuthenticated";
} else {
	
if (isset($_REQUEST["file"])) {
	$file=$config['site_filestore'].$_REQUEST["file"];
	if(file_exists($file)) {
//  header("Content-type: application/force-download");
//	header('Content-Type: application/octet-stream');
//  header("Content-Transfer-Encoding: Binary");
//  header("Content-length: ".filesize($file));
//  header('Content-disposition: attachment; filename="aaa.txt"');
//	readfile("$file");
//	} else {
//    echo "No file selected"; 
//	}

$download_rate = 20000.5;
if(file_exists($file) && is_file($file))
{
    header('Cache-control: private');
	//header("Content-Type: application/force-download");
	header('Content-Type: application/octet-stream');
	//header("Content-Transfer-Encoding: Binary");
    header('Content-Length: '.filesize($file));
    header('Content-Disposition: attachment; filename='.$download_file);
	readfile_chunked($file);
}
else 
{
    die('Error: The file '.$file.' does not exist!');
}
}
}
function readfile_chunked ($filename) {
  $chunksize = 1*(1024*1024); // how many bytes per chunk
  $buffer = '';
  $handle = fopen($filename, 'rb');
  if ($handle === false) {
    return false;
  }
  while (!feof($handle)) {
    $buffer = fread($handle, $chunksize);
    print $buffer;
  }
  return fclose($handle);
}
} 
 ?>