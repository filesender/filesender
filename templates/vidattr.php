<?php

global $vidattr;

$vidattr = "";

if (array_key_exists('vid', $_REQUEST)) {
    $vid = $_REQUEST['vid'];
    if( Utilities::isValidUID($vid) ) {
        $vidattr = "&vid=" . $vid;
    }
}

