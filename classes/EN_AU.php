<?php

/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 
// EN_AU language file for flex application
class EN_AU {

private static $instance = NULL;

	public static function getInstance() {
		// Check for both equality and type		
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	} 
	
public function language() {
	
$lang = array(

	// top menu buttons
	"Admin" => "Administration",
	"New_File" => "New Upload",
	"Vouchers" => "Vouchers",
	"Logon" => "Logon",
	"Log_Off" => "Log Off",
	"My_Files" => "My Files",
	
	//
	"Active_Vouchers" => "Active Vouchers",
	"Are_You_Sure_Resend" => "Are you sure you want to re-send this Email?",
	"Administration" => "Administration",
	"Browse" => "Browse",
	"Cancel" => "Cancel",
	"Click_on_Send" => "Click on Send",
	"Current_Valid_Vouchers" => "Current Valid Vouchers",
	"Complete_Log" => "Complete Log",
	"Date" => "Date",
	"Database_Connection" => "Database Connection",
	"Download" => "Download",
	"Download_File" => "Download File",
	"Downloads" => "Downloads",
	"Email_Sent" => 'Email has been sent',
	"Expiry_Date" => "Expiry date",
	"Export" => "Export",
	"Errors" => "Errors",
	"File" => "File",
	"File_Name" => "File Name",
	"File_Size" => "File Size",
	"Files_Available" => "Files Available",
	"File_to_Upload" => "File to Upload",
	"File_to_be_Redistributed" => "File to be Redistributed",
	"File_Storage" => "File Storage",
	"From" => "From",
	"Logging" => "Logging",
	"Gears_Status" => "Gears Status",
	"Gears_Upload" => "Gears Upload",
	"General" => "General",
	"loading" => "Loading.....",
	"Message" => "Message",
	"Optional" => "Optional",
	"Re-send" => "Re-send",
	"Resume" => "Resume",
	"Send_Vouchers_to" => "Send Vouchers to",
	"Send_Voucher" => "Send Voucher",
	"Send_File" => "Send",
	"Size" => "Size",
	"Status" => "Status",
	"Subject" => "Subject",
	"Temporary_File_Storage" => "Temporary File Storage",
    "To" => "To",
	"Upload" => "Upload",
	"Uploading" => "Uploading",
	"Uploads" => "Uploads",
	"Valid_To" => "Valid to",
	"Welcome" => "Welcome",
	
	"Voucher_Sent" => "Voucher Sent",
	"Vouchers_Sent" => "Vouchers Sent",
	"Voucher_has_been_sent" => "Voucher has been sent",
	"Voucher_ID" => "Voucher ID",
	"Your_file_has_been_sent" => "Your file has been uploaded and message sent.",
	"You_need_to_logon_to " => "You need to logon to ",
	"notAuthenticated" => "Unable to Authenticate - You need to logon again.",
	
	// steps
	"Enter_delivery_email_address" => "Enter delivery email address(es)",
	"Browse_for_a_file" => "Browse for a file",
	"Set_expiry_date" => "Set expiry date",
	"Select_Upload" => "Select Upload",

	// site help
	"site_help_text" => "Site help text",
	"Help" => "Help",
	
	
	"site_sendfileinstructions" => "<B>To send a file.</B><BR>Type an email address into the To: box<BR>Select BROWSE to choose a file on your computer.<BR>Select SEND FILE to upload and send the file.",
	"site_voucherinstructions" => "A Voucher allows someone to send you a file.<BR>To create a voucher, enter an email address then select Send Voucher.<BR>An email will be sent to the recipient with a link to use the Voucher.",
	
	// error messages
	"E000" => "Message", // Message
	"E001" => "Please enter an email address", // Email missing error message
	"E002" => "Error sending email - check your email address",		// Unable to send your file
	"E003" => "File size is too large",   // Error - file size is too large for gears upload
	"E004" => "Simple SAML is not configured correctly",  // Simple SAML is not configured correctly
	"E005" => "File storage location unavailable", // File storage location unavailable
	"E006" => "File temporary storage location unavailable", // File temporary storage location unavailable
	"E007" => "Log file location unavailable", //Log file location unavailable
	"E008" => "Cannot read/write storage location", // Cannot read/write storage location
	"E009" => "Cannot read/write temporary storage location", // Cannot read/write temporary storage location
	"E010" => "Javascript not enabled", //Javascript not enabled
	"E011" => "Unable to send email", // Unable to send email
	"E012" => "Unable to upload your file", // Unable to upload your file
	"E013" => "Drive space low", // Drive space low
	"E014" => "Drive space unavailable",
	"E015" => "Please check your email address", // Email missing error message
	"E016" => "Error uploading file - contact administrator", // Email missing error message
	"E017" => "Cannot move file", // Cannot move file
	"E018" => "Please check email address", //Please check email address
	"E019" => "Please browse for a file to upload",  // Please browser for a file to upload
	"E020" => "File is too large, Please install Google Gears (*see top right hand corner*) to upload large files.", // File is too large, install Google Gears to upload >2Gb files.
	"E021" => "You MUST agree to the terms and conditions.", // File is too large, install Google Gears to upload >2Gb files.
	"E022" => "The maximum number of email addresses allowed is ", // To many emails, maximum of x
	"E023" => "You are not authorised to upload a file.", // To many emails, maximum of x
	"E024" => "Unable to Browse for a file.", // To many emails, maximum of x
	"E025" => "This voucher is no longer valid.",
	"E026" => "Unable to Browse for a file.",
	"E027" => "Error sending email. Contact Administrator.",
	"E028" => "Unable to forward this file, voucher ID is unavailable. Contact Administrator (E028)",
	"E029" => "Your session may have timed out, please logon again and re-try or contact your administrator.",
	"E030" => "Cannot upload a file of 0 bytes. Please select another file."
);
return $lang;
}
}

?>
