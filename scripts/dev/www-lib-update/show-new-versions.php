<?php

$packages = array(
    'bootstrap'            => array(),
    'bootstrap-icons'      => array(),
    'bootbox'              => array(),
    'chart.js'             => array(),
    'flag-icons'           => array(),
    '@fortawesome/fontawesome-free' => array(),
    'jquery'               => array(),
    'jquery-ui-dist'       => array(),
    'kbpgp'                => array(),
    '@popperjs/core'       => array(),
    'promise-polyfill'     => array(),
    'streamsaver'          => array(),
    'webcrypto-shim'       => array(),
    'web-streams-polyfill' => array(),
    'xregexp'              => array(),
);
$json = file_get_contents(dirname(__FILE__) . '/package-lock.json');

$top = json_decode($json, true);
$deps = $top['packages']['']['dependencies'];

foreach( $packages as $pkg => $d ) {
    printf("%20s %10s\n", $pkg, $deps[$pkg]);
}
