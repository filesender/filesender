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
 *      notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
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

Logger::setProcess(ProcessTypes::ASYNC);
Logger::info('Send files to another server via FileSender pREST API started');

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
// Target transfer id
//
$target = (count($argv) > 1) ? $argv[1] : false;
$server = (count($argv) > 2) ? $argv[2] : false;
if (!$target) {
    echo "forward-pREST.php: no target-id\n";
    Logger::error("forward-REST.php: no target-id");
    exit(1);
}
if (!$server) {
    echo "forward-pREST.php: no server\n";
    Logger::error("forward-REST.php: no server");
    exit(1);
}
$mode   = (count($argv) > 3) ? $argv[3] : 'master';

$testingMode = false;//true;
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

//$server = ForwardAnotherServer::getServerByTransfer($transfer);
$server = ForwardAnotherServer::getServer($server);

$files = File::fromTransfer($transfer);
if(!$files) {
    Logger::error("Transfer files not found: $target");
    exit(1);
}

$chunk_size = isset($server['method_options']['chunk_size']) ? $server['method_options']['chunk_size'] : Config::get('upload_chunk_size');
$padding_size = $transfer->is_encrypted ? Config::get('upload_crypted_chunk_padding_size') : 0;

if ($mode == 'master') {
    $workers = isset($server['method_options']['workers']) ? $server['method_options']['workers'] : 8;

    $command = __DIR__.'/pREST/pREST "'.__FILE__.'" "'.$argv[1].'" "'.$argv[2].'" "'.$workers.'"';
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"], // stderr
    ];
    $pipes = [];
    $process = proc_open($command, $descriptorspec, $pipes);
    if (is_resource($process)) {
        foreach ($files as $file) {
            Logger::debug("creating job(s) for file upload: $file->name");

            $size = $transfer->is_encrypted ? $file->encrypted_size : $file->size;
            for($offset=0; $offset<=$size; $offset+=$chunk_size) {
                Logger::debug("job for file $file->id offset: $offset size: $file->size est chunk: ".min($chunk_size,$file->size-$offset));
                fwrite($pipes[0], $file->id.','.$offset."\n");
            }
        }
        fclose($pipes[0]);

        // Read from stdout, do we need this?
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $return_value = proc_close($process);
        if ($return_value != 0) {
            Logger::error("Something went wrong with the pREST script: $command, return value: $return_value");
            exit(1);
        }

        foreach ($files as $file) {
            Logger::debug("file: $file->name forwarded");
            $file->forwarded();
        }
    } else {
        Logger::error("Something went wrong with opening pREST process");
        exit(1);
    }

} else if ($mode == 'worker') {
    $line = explode(',',$argv[4]);
    //echo 'worker: '.print_r($line,true);
    $file=$files[$line[0]];
    $offset=$line[1];
    $stream = Storage::getStream($file);
    if (!$stream) {
        $path = Storage::buildPath($file) . $file->uid;
        throw new ForwardException('Cannot read storage: '.$path);
    }
    fseek($stream, $offset);
    $data = fread($stream, $chunk_size + $padding_size);
    Logger::debug("putFileChunk $file->id offset: $offset size: ".strlen($data)." total: $file->size");
    ForwardAnotherServer::putFileChunk($file, $data, $offset);
    fclose($stream);

} else {
    Logger::error("Mode incorrect: $mode");
    exit(1);
}
