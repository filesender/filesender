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
$lang = get_client_language($languages);

//Set a default language file
$lang_file = "EN_AU.php";
//Switch based on the language from the user to the language file
//If none present, we'll always have the default
switch($lang) {
	
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

setcookie("lang-chosen",$lang);// [, string value [, int expire [, string path [, string domain [, bool secure [, bool httponly]]]]]])

//Try and include the language file
require_once("$filesenderbase/language/".$lang_file);


//We might have an incomplete language file (not all keys defined)
//So first we define all required language keys, then loop over the, If one is not defined, we do it dynamically with a message indictaing which key is not defined. 

//NOTE!!!!!!!!!!!!!!!!!!!!!!!!!
//Constants not defined here won't be caught below!!!!

$required_lang_keys = array("_ADMIN","_NEW_UPLOAD","_VOUCHERS","_LOGON","_LOG_OFF","_MY_FILES","_HOME","Home","_ABOUT","_HELP","_VOUCHER_CANCELLED","_UPLOAD_COMPLETE","_DOWNLOAD","_GENERAL","_UPLOADS","_DOWNLOADS","_ERRORS","_FILES_AVAILABLE","_ACTIVE_VOUCHERS","_COMPLETE_LOG","_TO","_FROM","_SIZE","_CREATED","_FILE_NAME","_SUBJECT","_EXPIRY","_MESSAGE","_TYPE","_TERMS_OF_AGREEMENT","_SHOW_TERMS","_SELECT_FILE","_UPLOADING_WAIT","_UPLOAD","_BROWSE","_CANCEL","_SEND_NEW_VOUCHER","_EMAIL_SEPARATOR_MSG","_Active_Vouchers","_Are_You_Sure_Resend","_Administration","_Click_on_Send","_Current_Valid_Vouchers","_Complete_Log","_Date","_Database_Connection","_Download","_Download_File","_Downloads","_Email_Sent","_Expiry_Date","_Export","_Errors","_File","_File_Name","_File_Size","_Files_Available","_File_to_Upload","_File_to_be_Redistributed","_File_Storage","_From","_Logging","_Gears_Status","_Gears_Uplad","_General","_loading","_Message","_Optional","_Resend","_Resume","_Send_Vouchers_to","_Send_Voucher","_Send_File","_Size","_Status","_Subject","_Temporary_File_Storage","_To","_Upload","_Uploading","_Uploads","_Valid_To","_Welcome","_Voucher_Sent","_Vouchers_Sent","_Voucher_has_been_sent","_Voucher_ID","_Your_file_has_been_sent","_You_need_to_logon_to","_notAuthenticated","_Enter_delivery_email_address","_Browse_for_a_file","_Set_expiry_date","_Select_Upload","_site_help_text","_Help","_OPTIONAL");


foreach($required_lang_keys as $value){

	if(!defined($value)){
		define($value,"The translation for ".$value." is not defined");
	}
	
}

?>
