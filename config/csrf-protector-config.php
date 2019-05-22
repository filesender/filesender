<?php

// get access to normal config.
$configpath = __DIR__ ."/config.php";
include($configpath);


/**
 * Configuration file for CSRF Protector
 * Necessary configurations are (library would throw exception otherwise)
 * ---- logDirectory
 * ---- failedAuthAction
 * ---- jsUrl
 * ---- tokenLength
 */

return array(
	"failedAuthAction" => array(
		"GET" => 0,
		"POST" => 0),
	"errorRedirectionPage" => "",
	"customErrorMessage" => "",
	"tokenLength" => 10,
	"cookieConfig" => array(
		"path" => '',
		"domain" => '',
		"secure" => true,
		"expire" => 0,
	),
	"disabledJavascriptMessage" => "This site attempts to protect users against <a href=\"https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29\">
	Cross-Site Request Forgeries </a> attacks. In order to do so, you must have JavaScript enabled in your web browser otherwise this site will fail to work correctly for you.
	 See details of your web browser for how to enable JavaScript.",

        // This is a selection of GET requests to verify CSRF for
        // it is likely a good idea to share your updates in a PR
        // if you think they are generally good for security.
	 "verifyGetFor" => array(),

	 //
	 // The following should be set correctly from your config.php
	 // information
	"jsUrl" => $config['site_url'] . "/js/csrfprotector.js",
	"logDirectory" => FILESENDER_BASE.'/log/',

        // I found that leaving this with the _ as default
        // caused the implicit token to be stripped
        // in jQuery ajax calls. Leave it as this setting
        // unless you are willing to test the REST calls
        // with your change. See terasender_worker.js which
        // uses this value directly.
	"CSRFP_TOKEN" => "csrfptoken",
);
