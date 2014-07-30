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

require_once('../classes/_includes.php');

$functions = Functions::getInstance();

date_default_timezone_set(Config::get('Default_TimeZone'));

if (isset($_REQUEST["gid"])) {
    $fileArray = $functions->getMultiFileData($_REQUEST["gid"]);

    redirectIfSingleDownload();

    $zipper = new Zipper();

    foreach ($fileArray as $file) {
        if (shouldDownloadFile($file)) {
            $zipper->addFile($file);
        }
    }

    if (isset($_REQUEST["dlcomplete"]) && $_REQUEST['dlcomplete'] == 'true') {
        logEntry('dlcomplete value in multidownload.php: ' . $_REQUEST['dlcomplete']);
        $zipper->enableDownloadCompleteEmail();
    }
    $zipper->sendZip();
}

// If only one file was selected, redirect to the single-file download page. The $_REQUEST array contains the gid followed by
// the file voucher IDs, an "isformrequest" flag and the receipt checkbox value. Fetch the second key from the array to get the vid of the single file.
function redirectIfSingleDownload()
{
    if (count($_REQUEST) <= 4) {
        next($_REQUEST);
        header('Location: download.php?vid=' . key($_REQUEST));
        exit;
    }
}

// Returns true if the file is selected for download, false otherwise.
function shouldDownloadFile($file)
{
    return !isset($_REQUEST["isformrequest"]) || isset($_REQUEST[$file["filevoucheruid"]]);
}
