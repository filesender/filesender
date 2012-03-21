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

//Get locale override if it exists
if(file_exists("$filesenderbase/config/locale.php")) { 
require_once("$filesenderbase/config/locale.php"); 
} else {
require_once("$filesenderbase/language/locale.php");
}

//Set a default language file via the parameter.
//We distribute En-AU ALWAYS via the project!
function get_client_language($availableLanguages, $default='en-au'){
 
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
 
		$langs=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
 
		//start going through each one
		foreach ($langs as $value){
 
			//strtolower is needed for e.g. Chrome, that sends nl-NL
			$choice =  strtolower($value);
			if(in_array($choice, $availableLanguages)){
				return $choice;
			}
		}
	} 
	return $default;
}

//Get the language based on the browser accepted langauge and the avaialable locales
if(isset($config['site_defaultlanguage'])) {
	$langs = get_client_language(array_keys($locales), str_replace("_","-",strtolower($config['site_defaultlanguage'])));
} else {
	$langs = get_client_language(array_keys($locales));
}
// Set the language file
$lang_file = $locales[$langs];
//Try and include the language file
// default english
//By including en_AU first, we make sure ALL used keys actually exist!
require_once("$filesenderbase/language/". "en_AU.php");

if(isset($config['site_defaultlanguage']) &&  file_exists("$filesenderbase/language/".$config['site_defaultlanguage'].".php")) { require_once("$filesenderbase/language/".$config['site_defaultlanguage'].".php"); };
if(isset($config['site_defaultlanguage']) &&  file_exists("$filesenderbase/config/".$config['site_defaultlanguage'].".php")) { require_once("$filesenderbase/config/".$config['site_defaultlanguage'].".php"); };

require("$filesenderbase/language/". $lang_file);

// check for custom language files in config
// load custom language from config if it exists
if(file_exists("$filesenderbase/config/".$lang_file)) { require("$filesenderbase/config/".$lang_file); }

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
