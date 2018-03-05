<?php

$BASE = __DIR__ . '/../../';

function loadLang( $code ) {
    global $BASE;
    $n = 0; $kn = 0;
    $keys = array();
    echo "reading $BASE/language/$code/lang.php", "\n";
    foreach(explode("\n", file_get_contents("$BASE/language/$code/lang.php")) as $line) {
        $n++;
        
        if(!preg_match('`\$lang\[[\'"]([^\'"]+)[\'"]\]\s*=\s*[\'"](.*)[\'"];`', $line, $m)) continue;
        $kn++;
        $k = $m[1];
        $v = $m[2];
        echo "$k \n";
        if(array_key_exists($m[1], $keys)) {
            echo $m[1].' ('.$n.') already found at '.$keys[$m[1]]."\n";
        } else {
            $keys[$m[1]] = $v;
        }
    }
    return $keys;
}


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


function squote( $s ) {
    $s = str_replace( '\\', '\\\\', $s );
    $s = str_replace( "'", "\'", $s );
    return $s;
}

function write_translation_lang_file( $code, $lang ) {
    global $BASE;
    $fn = "$BASE/language/$code/lang.php";
    $data = '';

    ksort( $lang );
    foreach( $lang as $k => $v ) {
        $data .= "\$lang['" . $k . "'] = '" . squote($v) . "';\n";
    }

    echo "writing general translations to file at $fn \n";
    file_put_contents( $fn, "<?php\n" . $data );
    
}


function write_translation_term_file( $code, $term, $data ) {
    global $BASE;
    $p = "$BASE/language/$code/";
    $fn = '';
    foreach( array('html','mail','text') as $type ) {
        if( file_exists( "$BASE/language/master/$term.$type.php" )) {
            $fn = "$p/$term.$type.php";
        }
    }
    echo "translation file for $term at $fn \n";
    file_put_contents( $fn, $data );
}
