<?php

include_once(__DIR__ . '/common.php');


/*
 * This script exports all the translation terms for all languages to a JSON file
 * which can be used to update translations on poeditor.com
 */
if(count($argv) < 2) {
    die("\n"
      . "This script exports all the translation terms for all languages to a JSON file\n"
      . "which can be used to update translations on poeditor.com \n"
      . "\n"
      . " arg1 is the file to write the terms (one per line format) \n" );
}
$outputFilename = $argv[1];

$allterms = array();

$ldir = "$BASE/language";

foreach( scandir("$BASE/language") as $f ) {
    if( preg_match('/[^_]*_[^_]*/', $f ) && is_dir( "$ldir/$f" )) {
        echo "looking at language: $f\n";
        $code = $f;
        
        $lang = loadLang( $code );
        $langdir = loadLangDirectory( $code );

        $allterms = array_merge( $allterms, $lang );
        $allterms = array_merge( $allterms, $langdir );
    }
}

ksort($allterms);
file_put_contents($outputFilename,
                  implode("\n", array_keys($allterms)));
