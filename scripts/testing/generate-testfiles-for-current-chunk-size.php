<?php

require_once dirname(__FILE__).'/../../includes/init.php';

/* * Downloading single byte file
 *                   10 byte file
 *              chunk-1 byte file
 *              chunk   byte file
 *              chunk+1 byte file
 *            2*chunk-1 byte file  
 *            2*chunk   byte file  
 *            2*chunk+1 byte file
 *           10*chunk   byte file
 * */

$loc = $argv[1];
$locnote = false;
if( $loc == '' ) {
    $locnote = true;
    $loc = '/tmp/generate-testfiles-for-current-chunk-size-output';
}
$chunk_sz = intval(Config::get("upload_chunk_size"));
$crypted_chunk_sz = intval(Config::get("upload_crypted_chunk_size"));


echo "Generating some test files and hash files to verify them with current settings\n";
echo "\n";
echo "These will be saved in the directory $loc\n";
if( $locnote ) {
    echo " note that the first parameter to this script can nominate the location\n";
}
echo "\n";
echo "        chunk_sz: $chunk_sz \n";
echo "crypted_chunk_sz: $crypted_chunk_sz \n";

if(!file_exists($loc)) {
    mkdir($loc);
}
if(!file_exists($loc)) {
    echo "Unable to ensure $loc exists!\n";
    exit(1);
}
if(!chdir($loc)) {
    echo "Unable to change into directory at $loc\n";
    exit(1);
}

function writefile( $sz ) {
    global $chunk_sz;
    $maxchunk = floor($sz / $chunk_sz);
    $filename = "testfile-chunk$maxchunk-$sz";
    $data = random_bytes ( $sz );
    file_put_contents ( $filename, $data );
    file_put_contents ( "$filename.md5", md5($data) );
}



#
# write files of various "interesting" sizes for testing encryption functionality
#
$sizes = array( 1, 10,
                $chunk_sz-1, $chunk_sz, $chunk_sz+1,
                2*$chunk_sz-33, 2*$chunk_sz-32, 2*$chunk_sz-31,
                2*$chunk_sz-17, 2*$chunk_sz-16, 2*$chunk_sz-15,
                2*$chunk_sz-1, 2*$chunk_sz, 2*$chunk_sz+1,
                2*$chunk_sz+15, 2*$chunk_sz+16, 2*$chunk_sz+17,
                20*$chunk_sz, 20*$chunk_sz+1 );
foreach( $sizes as $sz ) {
    echo "generating $sz \n";
    writefile( $sz );
}










