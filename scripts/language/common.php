<?php

$BASE = __DIR__ . '/../../';

function loadLang( $code ) {
    global $BASE;
    $n = 0; $kn = 0;
    $keys = array();
    echo "reading $BASE/language/$code/lang.php", "\n";
    foreach(explode("\n", file_get_contents("$BASE/language/$code/lang.php")) as $line) {
        $n++;
        
        if(!preg_match('`\$lang\[[\'"]([^\'"]+)[\'"]\]\s*=`', $line, $m)) continue;
        $kn++;
        
        if(array_key_exists($m[1], $keys)) {
            echo $m[1].' ('.$n.') already found at '.$keys[$m[1]]."\n";
        } else {
            $keys[$m[1]] = $n;
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
