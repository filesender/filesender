<?php

$packages = array(
    'bootstrap'            => array(),
    'bootbox'              => array(),
    'chart.js'             => array(),
    'font-awesome'         => array(),
    'jquery'               => array(),
    'jquery-ui-dist'       => array(),
    'promise-polyfill'     => array(),
    'streamsaver'          => array(),
    'webcrypto-shim'       => array(),
    'web-streams-polyfill' => array(),
    'xregexp'              => array(),
    'flag-icon-css'        => array(),    
    '@popperjs/core'       => array(),
);
$json = file_get_contents(dirname(__FILE__) . '/package-lock.json');

$top = json_decode($json, true);
$deps = $top['dependencies'];

foreach( $packages as $pkg => $d ) {
    printf("%20s %10s\n", $pkg, $deps[$pkg]['version']);
}
