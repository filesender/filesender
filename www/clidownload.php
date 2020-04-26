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
 *  notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *  names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.
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

require_once('../includes/init.php');

$one=1;

$apipath=Config::get('site_url').'rest.php';
$daysvalid=Config::get('default_transfer_days_valid');

//
// This makes a config file for the python script to connect to this filesender
// server and authenticate as this user.
//
if( array_key_exists('config',$_GET) && $_GET['config']=='1' ) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="filesender.py.ini"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $username = Auth::user()->saml_user_identification_uid;
    $apikey   = Auth::user()->auth_secret;
    echo "[system]\n";
    echo "base_url = $apipath\n";
    echo "default_transfer_days_valid = $daysvalid\n";
    echo "\n";
    echo "[user]\n";
    echo "username = $username\n";
    echo "apikey = $apikey\n";
    return;
}

$script=file_get_contents('../scripts/client/filesender.py');


$script=str_replace('[base_url]',$apipath,$script,$one);
$script=preg_replace("/\ndefault_transfer_days_valid[ ]*=[ ]*[0-9]+\n/",
                     "\ndefault_transfer_days_valid = $daysvalid\n",
                     $script);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="filesender.py"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.strlen($script));

echo $script;
