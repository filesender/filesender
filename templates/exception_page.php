<?php

if(!array_key_exists('exception', $_REQUEST))
    throw new DetailedException('exception_not_found', '');

$exception = StorableException::unserialize($_REQUEST['exception']);

Template::display('exception', array('exception' => $exception));
