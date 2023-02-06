<?php

$earl = (count($argv) > 1) ? $argv[1] : '';
if( $earl=='' ) {
    echo "Usage: \n";
    echo "simplesamlphp-extract-sha256-from-release-notes.php release-notes-url\n";
    exit(1);
}

$page = file_get_contents($earl);
preg_match('/SHA256 checksum: ([a-f0-9]+)<.*/', $page, $matches );
if( count($matches) == 2 ) {
    echo $matches[1];
} else {
    echo "Not found\n";
    exit(1);
}


