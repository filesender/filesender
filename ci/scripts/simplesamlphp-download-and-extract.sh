#!/bin/bash
set -ev

VER=1.17.1
wget https://github.com/simplesamlphp/simplesamlphp/releases/download/v$VER/simplesamlphp-$VER.tar.gz --no-check-certificate

SHA_DOWNLOAD_HASH=$(sha256sum simplesamlphp-$VER.tar.gz | cut -d ' ' -f 1)
SHA_CHECK_HASH='d1a6e415828e8c257f9808a5b70d5f738f95af2633cdbae5cf8629571d33a803'


if [ "$SHA_DOWNLOAD_HASH" != "$SHA_CHECK_HASH" ]; then
    echo "Hashes did not match!!"
    echo "Downloaded hash $SHA_DOWNLOAD_HASH"
    echo "Verification hash $SHA_CHECK_HASH"
    exit 1
else
    echo "Hashes matched"
fi

tar xzf simplesamlphp-$VER.tar.gz
mv simplesamlphp-$VER/ simplesaml
