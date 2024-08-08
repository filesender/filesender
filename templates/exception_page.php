<?php

if(!array_key_exists('sid', $_REQUEST))
    throw new DetailedException('exception_not_found', '');

$sid = $_REQUEST['sid'];

if (!preg_match('`^[0-9a-f]{13}$`', $sid)) {
    throw new DetailedException('not_an_exception_identifier', $sid);
}

if (!array_key_exists('exception_'.$sid, $_SESSION)) {
    throw new DetailedException('unknown_exception_identifier', $sid);
}

$exception = $_SESSION['exception_'.$sid];
if (!($exception instanceof Exception)) {
    throw new DetailedException('not_an_exception', $sid);
}

$exception = $_SESSION['exception_'.$sid];

Template::display('exception', array('exception' => $exception));
