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

Logger::setProcess(ProcessTypes::CLI);
Logger::info('running av programs on new files');
proc_nice( 10 );

$inputs = array();
foreach(array_slice($argv, 1) as $arg) {
    $inputs[] = $arg;
}


$maxsizetoscan = Config::get("avprogram_max_size_to_scan");
$avprograms = AVProgram::getActiveProgramList();
$toobig = new AVProgramTooBig();

if( !count($avprograms)) {
    $emsg = "No AV programs are defined\n"
          . "see the docs for the avprogram_list configuration and try again!\n";
    Logger::error($emsg);
    echo $emsg;
    exit(1);
}

while( true ) {
    $limitPerIteration = 50;
    if(count($inputs)) {
        $fileList = array();
        foreach( $inputs as $id ) {
            $fileList[] = File::fromId($id);
        }
    } else {
        $fileList = File::findFilesWithoutAVResults( $limitPerIteration );
    }
    echo "have " . count($fileList) . " files to work on this time around\n";
    
    foreach( $fileList as $file ) {
        echo "Looking at file " . $file->id . "\n";
        if( !$file->is_encrypted ) {
            if( $file->size > $maxsizetoscan ) {
                $toobig->insepect( $file );
            } else {
                foreach( $avprograms as $prg ) {
                    $prg->inspect( $file );
                }
            }
        }
    }

    // if the user selected expicit files we are done now.
    if(count($inputs)) {
        exit(0);
    }

    echo "sleeping for a moment to avoid churn\n";
    sleep(10);
}

