#!/bin/bash
set -ev
# Create temp folder to download library:
echo "Creating Temp folder\n"
mkdir tmp
cd tmp
echo "Downloading SimpleSamlPhp 1.14.2\n"
wget https://simplesamlphp.org/res/downloads/simplesamlphp-1.14.2.tar.gz
# Extract it in a suitable directory and create symlink:
echo "Creating Opt folder\n"
cd ../
mkdir opt
cd opt
echo "Extracting SimpleSamlPhp in Opt folder\n"
tar xvzf /../tmp/simplesamlphp-1.14.2.tar.gz
echo "Creating SimpleSaml symlink\n"
ln -s simplesamlphp-1.14.2/ simplesaml
# Copy standard configuration files to the right places:
echo "Copy standard configuration files of SimpleSaml to the right places\n"
cd simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/