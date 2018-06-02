#!/bin/bash
set -ev

wget https://simplesamlphp.org/res/downloads/simplesamlphp-1.14.2.tar.gz --no-check-certificate

SHA_DOWNLOAD_HASH=$(sha256sum simplesamlphp-1.14.2.tar.gz)
SHA_CHECK_HASH='19b849065cdc8b96d74570b2ef91a08e72d0a4c0d9c30fa9526163ff6684c83e  simplesamlphp-1.14.2.tar.gz'


if [ "$SHA_DOWNLOAD_HASH" != "$SHA_CHECK_HASH" ]; then
    echo "Hashes did not match!!"
    echo "Downloaded hash $SHA_DOWNLOAD_HASH"
    echo "Verification hash $SHA_CHECK_HASH"
    exit 1
else
    echo "Hashes matched"
fi

tar xvzf simplesamlphp-1.14.2.tar.gz
ln -s simplesamlphp-1.14.2/ simplesaml
# Copy standard configuration files to the right places:
cd simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/
#cd lib
#readlink -f _autoload.php
#cd ../../..