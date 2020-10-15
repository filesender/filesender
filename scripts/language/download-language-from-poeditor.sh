#!/bin/bash
set -eou pipefail

SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

. ~/.filesender/poeditor-apikey

LANG=${1:?Supply poeditor language as arg1};
PHPFILE=${2:?Supply path to php file as arg2};
dirname=${3:?Supply directory path to use to store files as arg3};

echo "Downloading LANG  : $LANG "
echo "into directory at : $dirname "
cd "$dirname"
    
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

