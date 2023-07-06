#!/bin/bash
set -eou pipefail

SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

. ~/.filesender/poeditor-apikey

dirname=$(mktemp -d "/tmp/filesender-poeditor-imports-XXXXX");
cd "$dirname"
echo "Creating downloaded files in $dirname"

$SCRIPTDIR/download-language-from-poeditor.sh "cs"    "Czech"      $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "da"    "Danish"     $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "nl"    "Dutch"      $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "en-au" "English_AU" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "en"    "English_GB" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "et"    "Estonian"   $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "fi"    "Finnish"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "de"    "German"     $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "it"    "Italian"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "fa"    "Persian"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "pl"    "Polish"     $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ru"    "Russian"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "sl"    "Slovenian"  $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "es"    "Spanish"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "fr"    "French"     $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "sr"    "Serbian"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "tr"    "Turkish"    $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "pt-br"    "Portuguese_Brazilian" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ja"    "Japanese" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "hr"    "Croatian" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "si"    "Sinhalese" $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ur"    "Urdu"      $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "zh-Hans" "Chinese_Hans"      $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "zh-Hant" "Chinese_Hant"      $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "km"      "Khmer"             $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ne"      "Nepali"            $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "th"      "Thai"              $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "vi"      "Vietnamese"        $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ko"      "Korean"            $dirname
$SCRIPTDIR/download-language-from-poeditor.sh "ta"      "Tamil"             $dirname



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



