<?php

$BASE = __DIR__ . '/../../';

$warningAboutChangingFile = "<?php \n";
$warningAboutChangingFile .= "// WARNING, this is a read only file created by import scripts\n";
$warningAboutChangingFile .= "// WARNING\n";
$warningAboutChangingFile .= "// WARNING,  Changes made to this file will be clobbered\n";
$warningAboutChangingFile .= "// WARNING\n";
$warningAboutChangingFile .= "// WARNING,  Please make changes on poeditor instead of here\n";
$warningAboutChangingFile .= "// \n";
$warningAboutChangingFile .= "// \n";
$warningAboutChangingFile .= "?>\n";

function loadLangFile( $infilepath ) {
    global $BASE;
    $n = 0; $kn = 0;
    $keys = array();
    echo "reading $infilepath", "\n";
    foreach(explode("\n", file_get_contents("$infilepath")) as $line) {
        $n++;
        if(!preg_match('`\$lang\[[\'"]([^\'"]+)[\'"]\]\s*=\s*[\'"](.*)[\'"];`', $line, $m)) continue;
        $kn++;
        $k = $m[1];
        $v = $m[2];
//        echo "$k \n";
        if(array_key_exists($m[1], $keys)) {
            echo $m[1].' ('.$n.') already found at '.$keys[$m[1]]."\n";
        } else {
            $keys[$m[1]] = $v;
        }
    }
    return $keys;
}

function loadLang( $code ) {
    global $BASE;
    $n = 0; $kn = 0;
    $keys = array();
    $keys = loadLangFile("$BASE/language/$code/lang.php");
    return $keys;
}

function LangFilePath( $code ) {
    global $BASE;
    return "$BASE/language/$code/lang.php";
}

//
//
// see also resolveLangDirectoryReferences()
//
function loadLangDirectory( $code ) {
    global $BASE;

    $keys = array();
    echo "reading directory $BASE/language/$code/", "\n";

    foreach(scandir("$BASE/language/master") as $i) {
        if(preg_match('`^(.+)\.(html|mail|text)\.php$`', $i, $m)) {
            $keys[$m[1]] = $i;
        }
    }
    foreach(scandir("$BASE/language/$code") as $i) {
        if(preg_match('`^(.+)\.(html|mail|text)\.php$`', $i, $m)) {
            $keys[$m[1]] = $i;
        }
    }
    return $keys;
}

//
// This resolves references to files with the translation to the 
// text itself.
//
function resolveLangDirectoryReferences( $keys, $code ) {
    global $BASE;
    global $warningAboutChangingFile;
    $p = "$BASE/language/$code/";
    $fn = '';

    foreach ($keys as $term => $value) {

        foreach( array('html','mail','text') as $type ) {
            $fn = "$BASE/language/$code/$term.$type.php";
            if( file_exists( $fn )) {

                // This is not really ideal, the plan is to strip off the 
                // read only header added by write_translation_term_file()
                // without using any nasty eval() calls to run any php.
                //
                // Real php blocks and php blocks that do not close things 
                // as expected will either be passed verbatim or in the later
                // case might not be as expected.
                $lines = explode("\n",file_get_contents( $fn ));
                $data = "";
                $inComment = false;
                foreach( $lines as $l ) {
                    if( preg_match('/<\?php/', $l )) {
                        $inComment = true; 
                    }
                    if( preg_match('/\?>/', $l )) {
                        $inComment = false; 
                        continue;
                    }
                    if( !$inComment ) {
                        $data .= $l . "\n";
                    }
                }
                $keys[$term] = $data;
            }
        }
    }
    return $keys;
}

function squote( $s ) {

    $s = preg_replace("~(?<!\\\)'~", "\\'", $s);
    return $s;
}

function write_translation_lang_file( $code, $lang ) {
    global $BASE;
    global $warningAboutChangingFile;
    $fn = "$BASE/language/$code/lang.php";
    $data = '';

    ksort( $lang );
    foreach( $lang as $k => $v ) {
        $data .= "\$lang['" . $k . "'] = '" . squote($v) . "';\n";
    }

    echo "writing general translations to file at $fn \n";
    file_put_contents( $fn, $warningAboutChangingFile . "<?php\n" . $data );
    
}


function write_translation_term_file( $code, $term, $data, $context ) {
    global $BASE;
    global $warningAboutChangingFile;
    $p = "$BASE/language/$code/";
    $fn = "$p/$term.$context.php";
//    echo "translation file for $term at $fn \n";
    file_put_contents( $fn, $warningAboutChangingFile . $data );
}

