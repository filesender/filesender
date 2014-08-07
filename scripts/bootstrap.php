<?php

//Determines if the bootstrap.php is in a classes/<category>/t dir
// or in a the scripts dir. Stops execution if it's not in either.

if (substr(dirname(__FILE__), -1) == "t") {
    define ('FILESENDER_BASE', '../../../');
}
elseif (substr(dirname(__FILE__), -7) == "scripts") {
    define('FILESENDER_BASE', '../');
}
else {
    die('bootstrap.php is not in scripts or classes/<category>/t');
}

define('CLASSES', FILESENDER_BASE.'classes/');

//For activating the config params that require SERVER_NAME to be set
$_SERVER['SERVER_NAME'] = 'localhost';

require_once CLASSES.'autoload.php';

// autoloading Exception derived classes is at the time of writing not 
// bullet proof
require_once CLASSES.'exceptions/FileExceptions.class.php';
require_once CLASSES.'exceptions/CoreExceptions.class.php';
require_once CLASSES.'exceptions/StorageFileSystemExceptions.class.php';

