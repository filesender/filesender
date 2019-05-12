<?php

$packages = array(
    'chart.js'       => array(),
    'font-awesome'   => array(),
    'jquery'         => array(),
    'jquery-ui-dist' => array(),
);
$json = file_get_contents(dirname(__FILE__) . '/package-lock.json');

$top = json_decode($json, true);
$deps = $top['dependencies'];

foreach( $packages as $pkg => $d ) {
    printf("%15s %10s\n", $pkg, $deps[$pkg]['version']);
}
