<?php

/*
 * This script helps compare lang.php files. The format output by the
 * import-translation-for-language.php script has the lang array sorted.
 * This script will modify your nominated lang.php to be the same but sorted
 * in the same way so that diff can be more easily used to see what has
 * changed.
 *
 */

include_once(__DIR__ . '/common.php');

if(count($argv) < 2) {
    die("\n"
      . "This script will sort the lang.php file for a given language (en_AU for example)\n"
      . "\n"
      . "This allows you to more easily compare an old lang.php to one genereated by\n"
      . "import-translation-for-language.php because they are both sorted in the same way.\n"
      . "\n"
      . " arg1 is something like en_AU \n"
      . "\n"
    );
}

$code = (count($argv) > 1) ? $argv[1] : 'en_AU';
if(!is_dir("$BASE/language/$code")) die("$code lang does not exist under $BASE/language\n");

$lang = loadLang( $code );
echo "lang size ", count($lang), "\n";

write_translation_lang_file( $code, $lang );

