<?php

$vars = array('message' => array_key_exists('message', $_REQUEST) ? base64_decode($_REQUEST['message']) : Lang::tr('missing_data'));

if(array_key_exists('logid', $_REQUEST))
    $vars['logid'] = $_REQUEST['logid'];

Template::display('exception', $vars);
