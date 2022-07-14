<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2022, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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

require_once dirname(__FILE__).'/../includes/init.php';

$dbtype = Config::get('db_type');
$dbversion = "unknown";
$currentdt = date('m/d/Y h:i:s a', time());
$apachever = "unknown";

$s = DBI::prepare("select VERSION() as v");
$s->execute(array());
$r = $s->fetch();
$dbversion = $r["v"];


$output="";
exec('apachectl -v', $output, $retval);
$apachever = $output;
$apachever = implode("\n", $apachever );

$output="";
exec('lsb_release -i -r', $output, $retval);
$linuxver = $output;
$linuxver = implode("\n", $linuxver );

echo "------------------------------------------------------------\n";
echo "FileSender github issue version information\n";
echo "Generated at ".$currentdt."\n";
echo "\n";
echo "Database    type $dbtype \n";
echo "Database version $dbversion \n";
echo "\n";
echo "apache information\n";
echo "$apachever\n";
echo "\n";
echo "php version " . phpversion() . "\n";
echo "\n";
echo "Chrome  version ";  system("google-chrome --version");
echo "Firefox version ";  system("firefox --version");
echo "\n";
echo "$linuxver \n";
echo "------------------------------------------------------------\n";
