<?php

/*
 * This script imports a single language translation from poeditor
 * to the files contained in the language/en_AU or other selected lanuage
 *
 * You should feed this a php export from poeditor and the individual files
 * in the langauge/$argv[1] directory will be updated. These include
 *
 *    lang.php                           for small strings
 *    site_footer.html.php               for larger non email translations
 *    transfer_deleted_receipt.mail.php  for larger email translations
 *
 * For the larger translations, the name of the file forms the translation key
 * keys which are not matched to files are stored in lang.php.
 */

include_once(__DIR__ . '/common.php');

if(count($argv) < 2) {
    die("\n"
      . "This script updates the language translation files for a specific language\n"
      . "with an export from poeditor.com \n"
      . "\n"
      . "you might like to do this in a git branch so you can create\n"
      . "a pull request on github with your updated files\n"
      . "\n"
      . " arg1 is something like en_AU \n"
      . " arg2 is the name of the PHP exported translation from poeditor \n");
}


$code = (count($argv) > 1) ? $argv[1] : 'en_AU';
if(!is_dir("$BASE/language/$code")) die("$code lang does not exist under $BASE/language\n");

$transfile = (count($argv) > 2) ? $argv[2] : '';
if(!is_file($transfile)) die("please supply the PHP exported translation as argument 2\n");

include_once($transfile);
 
// filter NULLs
echo "LANG size1 ", count($LANG), "\n";
foreach($LANG as $ti => $t) {
    $term = $t['term'];
    $data = $t['definition'];
    if( $data == NULL ) {
       unset($LANG[$ti]);
    }
    
    // filter open box characters back to spaces.
    $data = str_replace( '␣', ' ', $data);
    if( $data ) {
        $LANG[$ti]['definition'] = $data;
    }
    
}
echo "LANG size2 ", count($LANG), "\n";


$lang = loadLang( $code );
echo "lang size ", count($lang), "\n";

$langdir = loadLangDirectory( $code );
echo "lang dir size ", count($langdir), "\n";



$existingTerms = $lang;



foreach($LANG as $ti => $t) {
    $term = $t['term'];
    $data = $t['definition'];

    // split the terms into mail context (to file) or into lang.php directly
    if( $t['context'] == 'mail' || $t['context'] == 'html' ) {
        write_translation_term_file( $code, $term, $data, $t['context'] );
    } else {
        $lang[$term] = $data;
        unset($existingTerms[$term]);
    }
}

write_translation_lang_file( $code, $lang );

if(count($existingTerms) > 0 ) {
    echo "------------------------------------------------------------\n";
    echo "WARNING some terms are only in local $code/lang.php file!\n";
    echo "------------------------------------------------------------\n";

    $grepRex = "/";
    echo "terms in local language translation but not in poeditor import " . count($existingTerms) . "\n";
    foreach($existingTerms as $idx => $t) {
//        echo "idx $idx term $t \n";
        if( $grepRex == "/" ) {
            $grepRex .= "('" . $idx . "'";
        } else {
            $grepRex .= "|'" . $idx . "'";
        }
    }
    $grepRex .= ")/";
    
    echo "\n The following entries exist only in the local lang.php file...\n\n";
    $mat = preg_grep( $grepRex, file(LangFilePath( $code )));
    $mat = implode( "", $mat );
    echo $mat;

    echo "------------------------------------------------------------\n";
    echo "WARNING some terms are only in local $code/lang.php file!\n";
    echo "------------------------------------------------------------\n";
}



