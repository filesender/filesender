<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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


require_once dirname(__FILE__).'/../../../includes/init.php';
require_once dirname(__FILE__).'/../../../optional-dependencies/s3/vendor/autoload.php';

use Aws\S3\S3Client;

$bucket_name = '5b264219-57e2-447d-c9c6-0a8d602f1ba5';
$folder_name = '';
$object_name = '000000000000000000';

$v = Config::get('cloud_s3_bucket');
if( $v && $v != '' ) {
    // include the slash here so it works if set or not set in the below code.
    $folder_name = $bucket_name . '/';
    $bucket_name = $v;
}

$client = S3Client::factory([
    'region'   => Config::get('cloud_s3_region'),
    'version'  => Config::get('cloud_s3_version'),
    'endpoint' => Config::get('cloud_s3_endpoint'),
    'use_path_style_endpoint' => Config::get('cloud_s3_use_path_style_endpoint'),
    'credentials' => [
         'key'    => Config::get('cloud_s3_key'),
         'secret' => Config::get('cloud_s3_secret'),
    ]
]);

$client->createBucket(array(
    'Bucket' => $bucket_name,
));

$result = $client->putObject(array(
    'Bucket' => $bucket_name,
    'Key'    => $folder_name . $object_name,
    'Body'   => 'Hello, world!'     
));
     
echo 'uploaded url is at ' . $result['ObjectURL'] . "\n";

$result = $client->getObject(array(
    'Bucket' => $bucket_name,
    'Key'    => $folder_name . $object_name,
));

echo 'downloaded result: ' . $result['Body'];

?>

