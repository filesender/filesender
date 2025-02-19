<?php

$jsonfile = $argv[1];
$phpfile = $argv[2];

echo "converting from JSON format in $jsonfile to php format in $phpfile\n";
$jsondata = file_get_contents($jsonfile);
$j = json_decode( $jsondata, true );


function quotedef($definition) {
    return "'" . preg_replace("~(?<!\\\)'~", "\\'", $definition) . "'";
}

$fp = fopen($phpfile, 'w');

fwrite($fp, "<?php  \n");
fwrite($fp, " \$LANG = array ( \n");

foreach ($j as $key => $value) {
    $term = $value['term'];
    $definition = $value['definition'];
    $context = $value['context'];

    if( $term == "serverlog_config_directive" ) {
        echo "DEF $definition\n";
    }
    
    fwrite($fp, "$key => \n");
    fwrite($fp, "array (\n");
    fwrite($fp, "  'term' => '".$term."',\n");
    fwrite($fp, "  'context' => '".$context."',\n");
    fwrite($fp, "  'definition' => ".quotedef($definition).",\n");
    fwrite($fp, "), \n");
}

fwrite($fp, " );\n");
fwrite($fp, "?>");
fclose($fp);


