<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
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

$filesenderbase = dirname(dirname(__FILE__));

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

//Get the language based on the browser accepted langauge
$languages = array("no","nl","en","en-au" ,"nl-nl","no-no","nb","nb-no");
$langs = get_client_language($languages);

//Set a default language file
$lang_file = "EN_AU.php";
global $lang;
//Switch based on the language from the user to the language file
//If none present, we'll always have the default
switch($langs) {
	
	case "nl-nl":
		global $lang_file;
		$lang_file = "NL_nl.php";
		break;
		
	case "nl":
		global $lang_file;
		$lang_file = "NL_nl.php";
		break;
		
	case "en-au":
		global $lang_file;
		$lang_file = "EN_AU.php";
		break;
		
	case "en":
		global $lang_file;
		$lang_file = "EN_AU.php";
		break;		
			
	case "no-no":
		global $lang_file;
		$lang_file = "NO_no.php";
		break;
		
	case "no":
		global $lang_file;
		$lang_file = "NO_no.php";
		break;		
		
	case "nb":
		global $lang_file;
		$lang_file = "NO_no.php";
		break;		
	
	case "nb-no":
		global $lang_file;
		$lang_file = "NO_no.php";
		break;		
	
}

setcookie("lang-chosen",$langs);// [, string value [, int expire [, string path [, string domain [, bool secure [, bool httponly]]]]]])


//Try and include the language file
// default english
if(isset($_REQUEST["lang"])) {
require_once("$filesenderbase/language/".$_REQUEST["lang"].".php");
} else {
require_once("$filesenderbase/language/EN_AU.php");
//Try and include the language file
require_once("$filesenderbase/language/".$lang_file);
}

// check for custom language files in config
// load EN_AU from config if it exists  
if(file_exists("$filesenderbase/config/EN_AU.php")) { require_once("$filesenderbase/config/EN_AU.php"); }

// load custom language from config if it exists
if(file_exists("$filesenderbase/config/".$lang_file)) { require_once("$filesenderbase/config/".$lang_file); }

function lang($item)
{
	global $lang;
	if (isset($lang[$item])) 
	{
	return mb_convert_encoding($lang[$item], "HTML-ENTITIES");	
	} else {
	return $item;
	}
}

?>
