#!/bin/bash
set -eou pipefail

. ~/.filesender/poeditor-apikey


projectid=${1:?supply poeditor project id as arg1. Main project is 633591 test project is 380345 };
langcode=${2:?supply poeditor language code as arg2};
jsonfile=${3:?supply path to json file created with convert-php-to-poeditor-json.php as arg3};

curl -X POST https://api.poeditor.com/v2/terms/add \
     -d api_token="$API_TOKEN" \
     -d id="$projectid" \
     --data-binary @$jsonfile

curl -X POST https://api.poeditor.com/v2/languages/update \
     -d api_token="$API_TOKEN" \
     -d id="$projectid" \
     -d language="$langcode" \
     --data-binary @$jsonfile


