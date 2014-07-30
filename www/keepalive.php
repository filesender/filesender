<?php
require_once('../classes/_includes.php');

$authsaml = AuthSaml::getInstance();
$authvoucher = AuthVoucher::getInstance();

date_default_timezone_set(Config::get('Default_TimeZone'));

	if ($authvoucher->aVoucher()) {
		logEntry("Voucher: * Keep alive *");
	}
	else if( $authsaml->isAuth()) {
		logEntry("Auth: * Keep alive *");
	} 
	
?>
