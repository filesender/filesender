<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 
 
global $lang;
global $locales;

$filesenderbase = dirname(dirname(__FILE__));

// Read browserpref to language file mappings from locale.php
// Prefer (exclusively) the one found in ./config (if any)
// This allows the site admin to both add new language mappings but also 
// to exclude mappings defined in the distributed locale.php.
if(file_exists("$filesenderbase/config/locale.php")) { 
	require_once("$filesenderbase/config/locale.php"); 
} else {
	require_once("$filesenderbase/language/locale.php");
}

// Function to map 'accept-language' browser tags to a language file
// Set a default language via the $default parameter.
function get_client_language($availableLanguages, $default='en-au')
{
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// Example: af,nl;q=0.9,en-us;q=0.8,en;q=0.7,de;q=0.6,it-ch;q=0.5,no;q=0.5,nb;q=0.4,sl;q=0.3,it;q=0.2,ar;q=0.1 
		$langs=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);

		//start going through each one
		foreach ($langs as $value){

			//Strip weight part (;q=..) if sent
			$value = explode(';',$value,2);
			//strtolower is needed for e.g. Chrome, that sends nl-NL
			$choice =  strtolower($value[0]);
			if(in_array($choice, $availableLanguages)){
				return $choice;
			}
		}
	}
	return $default;
}

// Get the language based on the browser accepted language(s) and the available locales
// Default to the site_defaultlanguage configurable if defined.
if(isset($config['site_defaultlanguage'])) {
	$langs = get_client_language(array_keys($locales), str_replace("_","-",strtolower($config['site_defaultlanguage'])));
} else {
	$langs = get_client_language(array_keys($locales));
}

// Set the language file if found in the locale.php mappings
if (!empty($locales[$langs])) {
	$lang_file = $locales[$langs];
	logEntry("Using ".$langs."/".$lang_file." as preferred language.","E_NOTICE");
} else {
	logEntry("No mapping for ".$langs." found. Please check your language configuration.","E_ERROR");
}

// Try and include the various language files:
// 1. Always read in the en_AU.php file first (this one should always be
//    available and contains all definitions)
// 2. Override with an existing $config['site_defaultlanguage'] file
//    (either in ./language or ./config)
// 3. Override with an existing language file found in the
//    browserpref to language file mappings in locale.php (either in
//    ./language or ./config)
// For 2. and 3.: if a language file exists in both ./language and ./config
// both files are included but for definitions specified in both files the
// one in /.config is used.

// 1. By including en_AU first, we make sure ALL used keys actually exist!
require_once("$filesenderbase/language/". "en_AU.php");

// 2. Override definitions if a non-en_AU default language is configured
if(isset($config['site_defaultlanguage']) && $config['site_defaultlanguage'] != "en_AU") 
{ 
	$deflangfileFound = FALSE;
	if( file_exists("$filesenderbase/language/".$config['site_defaultlanguage'].".php"))
	{
		require_once("$filesenderbase/language/".$config['site_defaultlanguage'].".php"); 
		$deflangfileFound = TRUE;
	} 
	if( file_exists("$filesenderbase/config/".$config['site_defaultlanguage'].".php"))
	{
		require_once("$filesenderbase/config/".$config['site_defaultlanguage'].".php"); 
		$deflangfileFound = TRUE;
	}
	if (!$deflangfileFound) 
	{
		logEntry("Default language file not available in language or config directory: ".$config['site_defaultlanguage'],"E_ERROR");
	}
}

// 3. Override definitions if a browserpref to language file mapping is found
if (!empty($lang_file)) {
	$preflangfileFound = FALSE;
	if(file_exists("$filesenderbase/language/".$lang_file))
	{
		require("$filesenderbase/language/". $lang_file);
		$preflangfileFound = TRUE;
	}

	// check for a custom language file in ./config and load custom definitions
	if(file_exists("$filesenderbase/config/".$lang_file))
	{
		require("$filesenderbase/config/".$lang_file);
		$preflangfileFound = TRUE;
	}
	if (!$preflangfileFound) 
	{
		logEntry("Mapping for preferred language found but language file not found in language or config directory: ".$lang_file,"E_ERROR");
	}
}

function lang($item)
{
	global $lang;
	if (isset($lang[$item])) 
	{
		return $lang[$item];	
	} else {
	return $item;
	}
}
?>
