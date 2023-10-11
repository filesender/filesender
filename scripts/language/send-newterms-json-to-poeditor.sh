#!/bin/bash
set -eou pipefail

. ~/.filesender/poeditor-apikey


projectid=${1:?supply poeditor project id as arg1. Main project is 633591 test project is 380345 };
jsonfile=${2:?supply path to json file created with export-terms-to-new-and-deleted-lists.sh as arg2};

curl -X POST https://api.poeditor.com/v2/terms/add \
     -d api_token="$API_TOKEN" \
     -d id="$projectid" \
     --data-binary @$jsonfile



