<?php

chdir( dirname(__FILE__) . '/../../../www/lib' );

echo "This script will inspect the software in www/lib in the current reposiroty\n";
echo "and show you the current versions in use.\n";
echo "\n";
echo "Working in directory " . getcwd() . "\n";
echo "\n";

//
// path is relative to www/lib.
//
// Note that the pattern regex must pick
// out the version into the first () match.
//
$packages = array(
    'chart.js' => array(
        'path' => 'chart.js/Chart.bundle.min.js',
        'pattern' => '/Chart.js v([0-9.]+).*/m',
    ),
    'font awesome' => array(
        'path' => 'font-awesome/css/font-awesome.css',
        'pattern' => '/Font Awesome ([0-9.]+) by/m',
    ),
    'jQuery' => array(
        'path' => 'jquery/jquery.min.js',
        'pattern' => '/jQuery v([0-9.]+) \| /m',
    ),
    'jQuery UI' => array(
        'path' => 'jquery-ui/jquery-ui.min.js',
        'pattern' => '/jQuery UI - v([0-9.]+) -/m',
    ),
    'reset' => array(
        'path' => 'reset/reset.css',
        'pattern' => '/v([0-9.]+) | 20/m',
    ),
    'promise-polyfill' => array(
        'path' => 'promise-polyfill/polyfill.min.js',
        'pattern' => '/v([0-9.]+)/m',
    ),
    'webcrypto-shim' => array(
        'path' => 'webcrypto-shim/webcrypto-shim.min.js',
        'pattern' => '/ WebCrypto API shim v([0-9.]+)/m',
    ),
    'xregexp' => array(
        'path' => 'xregexp/xregexp-all.js',
        'pattern' => "/XRegExp.version = '([0-9.]+)'/m",
    ),
    
);

foreach( $packages as $pkg => $d ) {
    $filecontents = file_get_contents($d['path']);
    $ver = "not found";
    if(preg_match_all($d['pattern'], $filecontents, $matches)){
        $ver = $matches[1][0];
    }

    printf("%15s %10s  %s\n", $pkg, $ver, $d['path']);
}
