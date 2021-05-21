#!/bin/bash

indir=${1:?Supply directory name that contains exported php files as arg1};
echo "importing poeditor exports from directory $indir";

function err {
	msg=$1
	echo " "
	echo " *** ERROR *** "
	echo " " 
	echo "$msg"
	exit
}

function importfile {
	langname=$1
	filename=$2

	echo "importing $langname from $filename..."
	if [ ! -d "../../language/$langname" ];  then
		err "language $langname doesnt exist. exiting...";
	fi
        php import-translation-for-language.php $langname "$indir/$filename" \
	 2>&1 | tee "$indir/${filename}_import.log";

}

# note that you need to update languages/locale.php to have 
# the new entries in it when you update the below
importfile cs_CZ FileSender_2.0_Czech.php
importfile da_DK FileSender_2.0_Danish.php
importfile nl_NL FileSender_2.0_Dutch.php
importfile en_AU FileSender_2.0_English_AU.php
importfile et_EE FileSender_2.0_Estonian.php
importfile fi_FI FileSender_2.0_Finnish.php
importfile de_DE FileSender_2.0_German.php
importfile it_IT FileSender_2.0_Italian.php
importfile fa_IR FileSender_2.0_Persian.php
importfile pl_PL FileSender_2.0_Polish.php
importfile ru_RU FileSender_2.0_Russian.php
importfile sl_SI FileSender_2.0_Slovenian.php
importfile es_ES FileSender_2.0_Spanish.php
importfile fr_FR FileSender_2.0_French.php
importfile sr_RS FileSender_2.0_Serbian.php
importfile tr_TR FileSender_2.0_Turkish.php

echo "Checking syntax of generated .php files, please wait..."
for f in $(find ../../language -type f -name \*.php)
do
	php -l $f | grep -v '^No syntax errors detected in '
done
