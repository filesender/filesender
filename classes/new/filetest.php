<?php

define('FILESENDER_BASE', dirname(dirname(dirname(__FILE__))));
echo "Environment defined... \r\n";

/* For testing purposes: */
$_SERVER['SERVER_NAME'] = "teratest.filesender.org";

echo "Server name set to: ".$_SERVER['SERVER_NAME']."\r\n";
require_once "File.class.php";

$file = File::getInstance();
print_r($file);
