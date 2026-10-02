<?php

include_once(__DIR__ . '/common.php');

if(count($argv) < 3) {
    die("\n"
      . "This script converts a one term per line file to a json file for upload to poeditor\n"
      . "\n"
      . " arg1 input terms (one per line format)\n"
      . " arg2 output json file\n"
        );
}

$inputFilename  = $argv[1];
$outputFilename = $argv[2];

$data = array();
$allterms = explode("\n", file_get_contents( $inputFilename ));
foreach( $allterms as $t ) {
   array_push( $data, array( 'term' => $t ));
   
}


var_dump($data);
file_put_contents($outputFilename, json_encode( $data ));

