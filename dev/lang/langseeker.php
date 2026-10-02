<?php

$required_lang = array_filter(explode("\n", file_get_contents('dev/lang/lang_sorted.txt')));
$required_emails = array_filter(explode("\n", file_get_contents('dev/lang/emails_sorted.txt')));

$code = (count($argv) > 1) ? $argv[1] : 'en_AU';

if(!is_dir('language/'.$code)) die($code.' lang does not exist under language/');

class Config {
    public static function get() {
        return '';
    }
}

$lang = array();
include 'language/'.$code.'/lang.php';
foreach(scandir('language/'.$code) as $i) {
    if(preg_match('`^(.+)\.(html|text)\.php$`', $i, $m))
        $lang[$m[1]] = '';
}
ksort($lang);

$emails = array();
foreach(scandir('language/'.$code) as $i) {
    if(preg_match('`^(.+)\.mail\.php$`', $i, $m))
        $emails[] = $m[1];
}
sort($emails);

echo 'There is '.count($required_lang).' required lang strings'."\n";
echo 'Found '.count(array_keys($lang)).' lang strings'."\n\n";

$missing = array_diff($required_lang, array_keys($lang));
$unused = array_diff(array_keys($lang), $required_lang);

$old_named = array();
foreach($unused as $u) {
    if(substr($u, 0, 1) != '_') continue;
    
    $nid = trim($u, '_');
    $lnid = strtolower($nid);
    $id = null;
    
    if(in_array($nid, $missing)) $id = $nid;
    if(in_array($lnid, $missing)) $id = $lnid;
    
    if(!$id) continue;
    
    $old_named[$id] = $u;
}

$missing = array_filter($missing, function($m) use($old_named) {
    return !array_key_exists($m, $old_named);
});

$unused = array_filter($unused, function($u) use($old_named) {
    return !in_array($u, $old_named);
});

$renamed = array();
foreach($unused as $u) {
    $ids = array_filter(array_keys($lang, $lang[$u]), function($id) use($u) {
        return $id != $u;
    });
    if(!count($ids)) continue;
    
    $renamed[$u] = $ids;
}

$unused = array_filter($unused, function($u) use($renamed) {
    return !array_key_exists($u, $renamed);
});

sort($missing);
sort($unused);
ksort($old_named);
ksort($renamed);

echo 'Missing lang strings ('.count($missing).') :'."\n\t".implode("\n\t", $missing)."\n\n";
echo 'Unused lang strings ('.count($unused).') :'."\n\t".implode("\n\t", $unused)."\n\n";
echo 'Old named lang strings ('.count($old_named).') :'."\n\t".implode("\n\t", $old_named)."\n\n";
echo 'Renamed lang strings ('.count($renamed).') :'."\n\t".implode("\n\t", array_map(function($from, $to) {
    return $from.' possibly to '.implode(' or ', $to);
}, array_keys($renamed), array_values($renamed)))."\n\n";





echo 'There is '.count($required_emails).' required email transations'."\n";
echo 'Found '.count($emails).' email transations'."\n\n";

echo 'Missing email translations :'."\n\t".implode("\n\t", array_diff($required_emails, $emails))."\n\n";
echo 'Unused email translations :'."\n\t".implode("\n\t", array_diff($emails, $required_emails))."\n\n";
