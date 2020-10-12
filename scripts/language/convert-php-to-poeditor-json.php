<?php

include_once(__DIR__ . '/common.php');
$ldir = "$BASE/language";

function usage() {
    die("\n"
      . "This script exports all the translation terms for all languages to a JSON file\n"
      . "which can be used to update translations on poeditor.com \n"
      . "\n"
      . " arg1 is the language to export (see ./language/ in the main repository)\n"
      . " arg2 is the file to write the json into\n"
      . " arg3 optional is preg_match() regex to select only matching terms. eg. '/^about.*/' \n"
      . " arg4 optional is max number of terms to select\n"
      . "\n"
      . "For example:  convert-php-to-poeditor-json  en_AU  /tmp/filesender-english-aus-translations.json "
    );
}

/*
 * This script exports all the translation terms for all languages to a JSON file
 * which can be used to update translations on poeditor.com
 */
if(count($argv) < 2) {
    usage();
}
$filesenderLang = $argv[1];
$outputFilename = $argv[2];
$termregex = ".*";
$max = 0;
$count = 0;
if( $argc >= 4 ) {
    $termregex = $argv[3];
}
if( $argc >= 5 ) {
    $max = $argv[4];
}


$allterms = array();

if(!(is_dir( "$ldir/$filesenderLang" ))) {
    echo "Directory not found $ldir/$filesenderLang \n";
    usage();
}

$response = array();

echo "looking at language: $filesenderLang\n";
$code = $filesenderLang;
        
$lang = loadLang( $filesenderLang );
$langdir = loadLangDirectory( $filesenderLang );
$langdir = resolveLangDirectoryReferences( $langdir, $filesenderLang );

foreach ($lang as $key => $value) {
    if( $max > 0 && $count >= $max ) {
        break;
    }
    $count++;

    if( preg_match($termregex, $key )) {
        $t = array( 'term' => $key, 'translation' => array('content'=>$value,'fuzzy'=>0));
        array_push($response,$t);
    }
}

foreach ($langdir as $key => $value) {
    if( $max > 0 && $count >= $max ) {
        break;
    }
    $count++;
    
    if( preg_match($termregex, $key )) {
        $t = array( 'term' => $key, 'translation' => array('content'=>$value,'fuzzy'=>0));
        array_push($response,$t);
    }
}

// write the output data
$fp = fopen($outputFilename, 'w');
fwrite($fp, 'data=' );
fwrite($fp, json_encode($response,JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
fwrite($fp, '' );
fclose($fp);
