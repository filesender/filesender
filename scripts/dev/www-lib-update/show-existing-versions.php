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
        'path' => 'chartjs/Chart.bundle.min.js',
        'pattern' => '/Version: ([0-9.]+).*/m',
    ),
    'font awesome' => array(
        'path' => 'font-awesome/css/font-awesome.css',
        'pattern' => '/Font Awesome ([0-9.]+) by/m',
    ),
    'jQuery UI' => array(
        'path' => 'jquery-ui-1.12.1.custom/jquery-ui.js',
        'pattern' => '/jQuery UI - v([0-9.]+) -/m',
    ),
    'reset' => array(
        'path' => 'reset/reset.css',
        'pattern' => '/v([0-9.]+) | 20/m',
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
