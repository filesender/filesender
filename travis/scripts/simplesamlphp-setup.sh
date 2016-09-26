#!/bin/bash
set -ev
# Create temp folder to download library:
mkdir downloads
cd downloads
wget https://simplesamlphp.org/res/downloads/simplesamlphp-1.14.2.tar.gz --no-check-certificate
ls
# Extract it in a suitable directory and create symlink:
cd ../
mkdir opt
cd opt
tar xvzf ../downloads/simplesamlphp-1.14.2.tar.gz
ls
ln -s simplesamlphp-1.14.2/ simplesaml
ls
# Copy standard configuration files to the right places:
cd simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/
ls
cd lib
ls
cd ../
ls