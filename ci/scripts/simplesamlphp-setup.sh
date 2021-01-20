#!/bin/bash
set -ev

echo "Copy files from SimpleSAMLphp extracted archive to the right places..."

# Copy standard configuration files to the right places:
cd simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/
#cd lib
#readlink -f _autoload.php
#cd ../../..
