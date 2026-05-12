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
importfile cs_CZ   FileSender_3.0_Czech.php
importfile da_DK   FileSender_3.0_Danish.php
importfile nl_NL   FileSender_3.0_Dutch.php
importfile en_AU   FileSender_3.0_English_AU.php
importfile en_GB   FileSender_3.0_English_GB.php
importfile et_EE   FileSender_3.0_Estonian.php
importfile fi_FI   FileSender_3.0_Finnish.php
importfile de_DE   FileSender_3.0_German.php
importfile it_IT   FileSender_3.0_Italian.php
importfile fa_IR   FileSender_3.0_Persian.php
importfile pl_PL   FileSender_3.0_Polish.php
importfile ru_RU   FileSender_3.0_Russian.php
importfile sl_SI   FileSender_3.0_Slovenian.php
importfile es_ES   FileSender_3.0_Spanish.php
importfile fr_FR   FileSender_3.0_French.php
importfile sr_RS   FileSender_3.0_Serbian.php
importfile tr_TR   FileSender_3.0_Turkish.php
importfile pt_BR   FileSender_3.0_Portuguese_Brazilian.php
importfile ja_JP   FileSender_3.0_Japanese.php
importfile hr_HR   FileSender_3.0_Croatian.php
importfile si_LK   FileSender_3.0_Sinhalese.php
importfile ur_PK   FileSender_3.0_Urdu.php
importfile zh_HANS FileSender_3.0_Chinese_Hans.php
importfile zh_HANT FileSender_3.0_Chinese_Hant.php
importfile km_KH   FileSender_3.0_Khmer.php
importfile ne_NP   FileSender_3.0_Nepali.php
importfile th_TH   FileSender_3.0_Thai.php
importfile vi_VN   FileSender_3.0_Vietnamese.php
importfile ko_KR   FileSender_3.0_Korean.php
importfile ta_TAM  FileSender_3.0_Tamil.php
importfile uk_UK   FileSender_3.0_Ukranian.php


echo "Checking syntax of generated .php files, please wait..."
for f in $(find ../../language -type f -name \*.php)
do
	php -l $f | grep -v '^No syntax errors detected in '
done
