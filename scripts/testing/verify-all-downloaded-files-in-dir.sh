#!/bin/bash

loc="${1:-/tmp/generate-testfiles-for-current-chunk-size-output}"
echo "using generated test files from location $loc"

filesloc="${2:-/tmp}"
echo "downloaded files should be found at: $filesloc"

for if in "$filesloc"/testfile-chunk*; 
do 
    php ./verify-testfile-download-hash.php "$loc" "$if"; 
done | less -R

