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


require_once(dirname(__FILE__).'/../../includes/init.php');

$method=substr($_SERVER['SCRIPT_FILENAME'],8,-4);

Logger::setProcess(ProcessTypes::ASYNC);
Logger::info("Send files to another server via $method started");

//
// Error handler
//
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno]: $errstr in $errfile on line $errline";
    Logger::error($message);
    exit(1);
}
set_error_handler("customErrorHandler");


//
// False by default, if present it is set
//
function getBoolArg( $name )
{
    global $argv;
    
    $ret = (count($argv) > 1) ? $argv[1]==$name : false;
    if( !$ret && count($argv) > 2 ) {
        $ret = ($argv[2]==$name);
        if( !$ret && count($argv) > 3 ) {
            $ret = ($argv[3]==$name);
        }
    }
    return $ret;
}

//
// Target transfer id
//
$target = (count($argv) > 1) ? $argv[1] : false;
$server = (count($argv) > 2) ? $argv[2] : false;
if (!$target) {
    echo "forward-$method.php: no target-id\n";
    Logger::error("forward-$method.php: no target-id");
    exit(1);
}
if (!$server) {
    echo "forward-$method.php: no server\n";
    Logger::error("forward-$method.php: no server");
    exit(1);
}

//
// Mainly a developer feature. Do not send emails to allow rapid testing
//
$testingMode = getBoolArg('--testing-mode'); // (count($argv) > 1) ? $argv[1]=='--testing-mode' : false;
//$testingMode = true;
if( $testingMode ) {
    Mail::TESTING_SET_DO_NOT_SEND_EMAIL();
}


Logger::debug("starting up... target transfer id:$target --testing-mode:$testingMode");
Logger::debug("running as user: " . `id` . "\n");

$transfer = Transfer::fromId($target);

if(!$transfer || $transfer->status != TransferStatuses::FORWARDING &&
   !($testingMode && $transfer->status == TransferStatuses::AVAILABLE)) {
    Logger::error("Transfer status is not UPLOADING: $target");
    exit(1);
}
Logger::debug("transfer: $transfer");

$method_config = ForwardAnotherServer::getServerMethodConfig($transfer,$method);
Logger::debug('method_config: '.print_r($method_config,true));

$files = File::fromTransfer($transfer);
if(!$files) {
    Logger::error("Transfer files not found: $target");
    exit(1);
}

$server = ForwardAnotherServer::getServerByTransfer($transfer);
if(!$server || !isset($server['hostname'])) {
    Logger::error("hostname not defined: $server");
    exit(1);
}
$hostname = $server['hostname'];

$remote_user = isset($method_config['method_options']['remote_user']) ? $method_config['method_options']['remote_user'].'@' : '';
$remote_dir = isset($method_config['method_options']['remote_dir']) ? $method_config['method_options']['remote_dir'] : '.';
$params = is_array($method_config['method_params']) ? implode(' ', $method_config['method_params']) : '';

$command = Config::get('storage_filesystem_forward_scp_command');

foreach ($files as $file) {
    Logger::debug("file: $file->name");
    $path = Storage::buildPath($file) . $file->uid;
    $output = [];
    Logger::debug("$command $params $path $remote_user$hostname:$remote_dir");
    exec("$command $params $path $remote_user$hostname:$remote_dir", $output, $rc);
    Logger::debug($rc);
    Logger::debug($output);
    if ($rc) {
        Logger::error("SCP command failed:");
        Logger::error($output);
        return;
    }
    $file->forwarded();
}

// Need to make the transfer available (sends email to recipients) ?
$transfer->makeAvailable();
