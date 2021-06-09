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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}
require_once dirname(__FILE__).'/../../includes/init.php';
require_once dirname(__FILE__).'/../../lib/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 */
class AVProgramURL extends AVProgram
{
    public function inspect( $file )
    {
        $appid = DBConstantAVProgram::lookup( DBConstantAVProgram::URL );
        echo "AVProgramURL on file " . $file->id . " size " . $file->size . " \n";
        $passes = false;
        $error = true;
        $emsg = "";

        
        $avprogram_url = $this->url;

        try {
            $h = new Client([
                'base_uri' => 'http://localhost',
            ]);
            $iss = $file->getStream();
            $response = $h->request( 'POST', $avprogram_url,
                                     ['body' => $iss] );
            if ($body = $response->getBody()) {
                echo "body: $body \n";
                $r = json_decode($body);
                if( !$r ) {
                    $emsg = "no json response";
                } else {
                    $passes = $r->passes;
                    $error = $r->error;
                    $emsg = $r->reason;
                }
            } else {
                $emsg = "no reply from URL";
            }
        }
        catch (Exception $e) {
            $emsg = $e->getMessage();
        }
        echo "adding result " . $file->id . " $appid $passes $error $emsg \n";
        $result = AVResult::create( $file, $appid, $this->name, $passes, $error, $emsg );
    }
    
};

