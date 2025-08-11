<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2025, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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

Logger::setProcess(ProcessTypes::CRON);
Logger::info('Cron started');


// import from Shibboleth
if( Config::get('auth_sp_type') == 'shibboleth' ) {
    echo "Alpha code for importing metadata from shibboleth\n";

    $context = stream_context_create(array(
        "ssl"=>array(
            "verify_peer"=>true,
            "verify_peer_name"=>true,
        ),
    ));
    
    $discoURL = Config::get("auth_sp_shibboleth_disco_url");
    $mdquery = Config::get("auth_sp_shibboleth_mdquery");
    $mdToCapture = Config::get('auth_sp_idp_metadata_to_capture');

    if( $discoURL == '' || $discoURL == null ) {
        echo "please set auth_sp_shibboleth_disco_url\n";
        exit;
    }   
    if( $mdquery == '' || $mdquery == null ) {
        echo "please set auth_sp_shibboleth_mdquery\n";
        exit;
    }
    
    $disco = file_get_contents( $discoURL, false, $context );
    $d = json_decode($disco);

    echo "looping over discovered entity IDs...\n";
    foreach( $d as $idx => $obj ) {
        $e = $obj->entityID;
        echo "entityid: $e \n";


        // /opt/shibboleth-sp/bin/mdquery -e urn:x-simplesamlphp:sspdev-idp
        $output = array();
        exec("$mdquery -e $e", $output);

        echo "executed mdquery for entityid: $e ". " resulting in this many lines of output: " . count($output) . " \n";

        $emd = simplexml_load_string( implode("\n",$output));

        $idp = IdP::ensure($e);

        foreach( $mdToCapture as $k => $fsk ) {
            $colname = $fsk;
            if( is_numeric($k)) {
                $k = $fsk;
            }
            $result = $emd->xpath('//md:'.$k.'[1]');
            $data = reset($result);

            if( $data ) {
                $idp->{$colname} = $data;
            }
            echo "setting k $colname to $data \n";
            
        }
        
        $idp->saveIfChanged();
    }

    echo "Alpha code for importing metadata from shibboleth run is complete\n";
    exit;
}



// Import from SSP
AuthSPSaml::loadSimpleSAML();

$c = new ReflectionClass("\SimpleSAML\Metadata\MetaDataStorageHandler");
$method = $c->getMethod('getMetadataHandler');
$mdh = $method->invoke(null);
$sspmd = $mdh->getList('saml20-idp-remote', true );

foreach( $sspmd as $entityId => $md ) {
    if( $entityId ) {
        echo "Importing metadata for $entityId ...\n";
        $idp = IdP::ensure($entityId);

        $force = true;
        AuthSPSaml::ensureLocalIdPMetadata( $entityId, $idp, $force );        
    }
}

