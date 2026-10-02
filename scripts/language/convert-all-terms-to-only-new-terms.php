<?php

include_once(__DIR__ . '/common.php');

if(count($argv) < 4) {
    die("\n"
      . "This script takes an export performed by export-all-terms.php and\n"
      . "a downloaded export from poeditor in php format and ouputs\n"
      . "a list of terms that are new in the github code but not already on poeditor\n"
      . "\n"
      . " arg1 is the all terms export from export-all-terms.php\n"
      . " arg2 is a php export from poeditor\n"
      . " arg3 is where to write the list of new terms (one per line format)\n"
        );
}

$alltermsFilename = $argv[1];
$transfile = $argv[2];
$outputFilename = $argv[3];
if(!is_file($transfile)) die("please supply the PHP exported translation as argument 2\n");

include_once($transfile);
$allterms = explode("\n", file_get_contents( $alltermsFilename ));

$removeTerms = array();
foreach($LANG as $ti => $t) {
    $term = $t['term'];
    $data = $t['definition'];
    array_push( $removeTerms, $term );
}

$allterms = array_diff( $allterms, $removeTerms );
$allterms = array_values( $allterms );
$allterms = array_flip( $allterms );
var_dump( $allterms );

ksort($allterms);
file_put_contents($outputFilename, 
                  implode("\n", array_keys($allterms)));
