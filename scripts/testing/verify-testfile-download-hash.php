<?php

require_once dirname(__FILE__).'/../../includes/init.php';

$loc = $argv[1];
if( $loc == '' ) {
    $loc = '/tmp/generate-testfiles-for-current-chunk-size-output';
}
$downloadedfilename = $argv[2];
if( $downloadedfilename == '' ) {
    echo "supply file to validate as arg 2\n";
    exit(1);
}

// Just make life simple by not allowing this combination right now
if( preg_match('=[(.][^/]*/.*=',$downloadedfilename)) {
    echo "This script does not support directories with . or ( in them at the moment\n";
    exit(1);
}
$testfile = preg_replace('_[(.].*_','',$downloadedfilename);
$testfile = $loc . '/' . preg_replace('_.*/_','',$testfile);

echo "generated test file tree at $loc\n";
echo "verifying downloaded file at $downloadedfilename\n";
echo "        against base file at $testfile\n";

$downloadeddata = file_get_contents ( $downloadedfilename );
$downloadhash = md5($downloadeddata);
$testhash = file_get_contents( "$testfile.md5" );
echo "          downloaded file hash $downloadhash\n";
echo "        against base file hash $testhash\n";
echo "\n";
if( $downloadhash == $testhash ) {
    echo "\033[32m SUCCESS \033[0m\n";
} else {
    echo "\033[31m FAIL \033[0m\n";
}
echo "\n";








