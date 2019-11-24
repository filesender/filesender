#!/bin/bash

SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

. ~/.filesender/poeditor-apikey


function downloadAndConvert {
    LANG=$1
    PHPFILE=$2
    dirname=$3

    echo "LANG $LANG "
    echo "dir $dirname "
    
    data=$(curl -s -X POST https://api.poeditor.com/v2/projects/export \
                -d api_token="$API_TOKEN" \
                -d id="$PROJECT_ID" \
                -d language="$LANG" \
                -d type="json"
        );

    if [ 'success' != "$(echo $data | jq -r .response.status)" ]; then
        echo "$data"
        echo ""
        echo "BAD SERVER RESPONSE FOR LANG $LANG"
        exit 1
    fi

    earl="$(echo $data | jq -r .result.url)";
    curl  "$earl" --output "FileSender_2.0_$LANG.json"
    php "$SCRIPTDIR/convert-poeditor-json-to-php.php" "FileSender_2.0_$LANG.json" "FileSender_2.0_$PHPFILE.php"
}

dirname=$(mktemp -d "/tmp/filesender-poeditor-imports-XXXXX");
cd "$dirname"
echo "Creating downloaded files in $dirname"


downloadAndConvert "cs" "Czech"     $dirname
downloadAndConvert "da" "Danish"    $dirname
downloadAndConvert "nl" "Dutch"     $dirname
downloadAndConvert "en-au" "English_AU" $dirname
downloadAndConvert "et" "Estonian"  $dirname
downloadAndConvert "fi" "Finnish"   $dirname
downloadAndConvert "de" "German"    $dirname
downloadAndConvert "it" "Italian"   $dirname
downloadAndConvert "fa" "Persian"   $dirname
downloadAndConvert "pl" "Polish"    $dirname
downloadAndConvert "ru" "Russian"   $dirname
downloadAndConvert "sl" "Slovenian" $dirname
downloadAndConvert "es" "Spanish"   $dirname
downloadAndConvert "fr" "French"    $dirname



ls -lh "$dirname"

echo "Checking syntax of generated .php files, please wait..."
for f in $(find "$dirname" -type f -name \*.php)
do
	php -l $f
done

echo ""
echo "running import-langs-directory.sh $dirname"
echo ""

cd "$SCRIPTDIR"
"$SCRIPTDIR/import-langs-directory.sh" "$dirname"



