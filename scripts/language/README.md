# Sending strings from PHP files to POEditor

If you have a PR for example that contains language translations for
new strings you might like to import these into POEditor through
scripts.

You will need to store the poeditor API TOKEN in a file in your home
directory to import strings. For example, using something like:

```
$ vi ~/.filesender/poeditor-apikey
export API_TOKEN="fixme"
export PROJECT_ID="633591"
```

The project id should be fine to set as shown above as this will inform
scripts to use the main FileSender poeditor project. Some scripts that might
perform bulk writes to poeditor may force you to explicitly select the project
on poeditor to update. This is done so that you can update either the real
translations or operate on a "testing" project to see the result of the actions
without changing the main poeditor translations. The test project has a project
id of 380345.

Then you can create a JSON file with all the translations for a
language using something like the following. Note that the en_AU is
the name of the directory in ../../languages

```
php convert-php-to-poeditor-json.php en_AU /tmp/test.json
```

Note that send-json command below will clobber any existing
translation on poeditor for a term for the language you select. To
allow some finer grained control over the terms to upload you can
optionally pass a PHP preg_match() regex to the
convert-php-to-poeditor-json.php call to select only specific terms.
This can be used to reduce the number of terms that are sent and thus
reduce the risk of overriding translations that may have been updated
by other people.

The below example shows how to select only some terms, in this case
only terms that start with the characters 'about'. Note that the
beginning slash and ending slash are needed in the regex as it is
passed directly to preg_match().

```
php convert-php-to-poeditor-json.php en_AU /tmp/test.json '/^about.*/'
```

Feel free to trim or filter the exported JSON file to only contain the
translations you are interested in uploading. To then upload those
translations to poeditor use the following. en-au is the poeditor
language code. The 633591 number tells the script to upload to the main
translation project on poeditor. If you are unsure of the outcome you
might like to first use the number 380345 which will upload to the
"Testing" poeditor translation project so you can see what happens
without impacting existing real translations.

```
# commentline    main project = 633591    test project = 380345

./send-json-translations-for-language-to-poeditor.sh 633591 en-au /tmp/test.json
```


Another useful code flow is to use the import-all-from-poeditor.sh script to download
the translations for all terms for every language and use those files to only export
translations from local files that are not already on poeditor.

The below uses a permissive regex and a 0 term max and explicitly passes the English_AU
translations that poeditor knows in order to not export existing terms. This should create
a file with only translations in your ../language/en_AU directory that are not known
to poeditor. Handy for sending terms from a pull request to poeditor for example. The
output can be sent using send-json-translations-for-language-to-poeditor.sh as detailed above.

```
php convert-php-to-poeditor-json.php \
       en_AU /tmp/test.json '/.*/' 0 \
       /tmp/filesender-poeditor-imports-wmsnC/FileSender_3.0_English_AU.php
```

To generate the FileSender_3.0_English_AU.php file from the current data on poeditor
one can use the below. See import-all-from-poeditor.sh for a list of poeditor language codes
that are currently imported in bulk.

```
mkdir -p /tmp/filesender-poeditor-imports-wmsnC
download-language-from-poeditor.sh "en-au" "English_AU" /tmp/filesender-poeditor-imports-wmsnC
```

