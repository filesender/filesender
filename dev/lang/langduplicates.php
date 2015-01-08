<?php

$code = (count($argv) > 1) ? $argv[1] : 'en_AU';

if(!is_dir('language/'.$code)) die($code.' lang does not exist under language/');

$keys = array();
$n = 0;
$kn = 0;

foreach(explode("\n", file_get_contents('language/'.$code.'/lang.php')) as $line) {
    $n++;
    
    if(!preg_match('`\$lang\[[\'"]([^\'"]+)[\'"]\]\s*=`', $line, $m)) continue;
    $kn++;
    
    if(array_key_exists($m[1], $keys)) {
        echo $m[1].' ('.$n.') already found at '.$keys[$m[1]]."\n";
    } else {
        $keys[$m[1]] = $n;
    }
}

foreach(scandir('language/'.$code) as $i) {
    if(preg_match('`^(.+)\.(html|text)\.php$`', $i, $m))
        if(array_key_exists($m[1], $keys)) {
            echo $m[1].' ('.$n.') already found at '.$keys[$m[1]]."\n";
        } else {
            $keys[$m[1]] = $i;
        }
}

echo 'Found '.$kn.' strings'."\n";
