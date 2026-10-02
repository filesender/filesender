<?php

//
// This simple script will check two php translation files to see if they contain the same translations.
//
// Usage:
//  php compare-php-lang-files.php generated_translation_from_json.php /tmp/Filesender_file_from_web_export_on_poeditor.php
//
// Note that this is rough and ready, it is mainly for testing that the terms as loaded from
// both a generated and manually downloaded file are the same, ignoring extra data that might
// be in the web download like context strings and the like.
//

$phpfile1 = $argv[1];
$phpfile2 = $argv[2];

function findTerm( $term, $lang ) {
    foreach ($lang as $ti => $t) {
        if( $t['term'] == $term ) {
            return $t;
        }
    }
    return null;
}

function test12( $lang1, $lang2 )
{
    foreach ($lang1 as $ti => $t) {
        $term = $t['term'];
        $t2 = findTerm( $t['term'], $lang2 );
        if( !$t2 ) {
            echo "term $term is missing from one of the translations...\n";
        } else {
            if( $t['definition'] != $t2['definition'] ) {
                echo "term $term has different definition...\n";
            }
        }
    }
}

include($phpfile1);
$lang1 = $LANG;
include($phpfile2);
$lang2 = $LANG;

test12( $lang1, $lang2 );


include($phpfile1);
$lang1 = $LANG;
include($phpfile2);
$lang2 = $LANG;

test12( $lang2, $lang1 );

