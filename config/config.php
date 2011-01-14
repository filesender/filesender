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

class config {

private static $instance = NULL;

	public static function getInstance() {
		// Check for both equality and type		
		if(self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	} 
	
public function loadConfig() {
	
	$config = array();

	// set  configs
	// v Beta 0.1.10 additions
	$config['postgresdateformat'] = "Y-m-d H:i:sP";
	$config['datedisplayformat'] = "DD-MM-YYYY";
	$config["crlf"] = "\n"; // for email CRLF can be changed to \r\n if required 
	$config["site_splashtext"] = "FileSender is a secure way to share large files with anyone! Logon to upload your files or invite people to send you a file.";

	$config["max_email_recipients"] = 100; // maximum email addresses allowed to send at once for voucher or file sending, a value of 0 allows unlimited emails.
	$config["server_drivespace_warning"] = 20; // as a percentage 20 = 20% space left on the storage drive

	// UI Settings
	$config["versionNumber"] = true;
	$config['about'] = true;
	$config['site_showStats'] = false;
	$config['displayUserName'] = true; 
	$config["help_link_visible"] = true;
	
	$config['aboutURL'] = "about.php";
	$config['helpURL'] = "help.php";
	
	// debug 
	$config["debug"] = false;
	$config['dnslookup'] = true; // log includes DNS lookup true/false
	$config["client_specific_logging"] = false; // client logging true/false
	$config["client_specific_logging_uids"] = ""; // "" is log all clients, or log for specific userid's or voucheruid's seperated by comma 'xxxx,zzzzz'

	// saml settings
	$config['saml_email_attribute'] = 'mail';
	$config['saml_name_attribute'] = 'cn';
	$config['saml_uid_attribute'] = 'eduPersonTargetedID';

	// Aup	
	$config["AuP_default"] = false; //AuP value is already ticked
	$config["AuP"] = true; // Aup is displayed
	$config["AuP_label"] = "I accept the terms and conditions of this service";
	$config["AuP_terms"] = "AuP Terms and conditions";
		
	$config['voucherRegEx'] = "'[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}'";
	$config['voucherUIDLength'] = 36;
	$config['emailRegEx'] = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
	$config['ban_extension'] = 'exe,bat';
	$config['admin'] = '';
	$config['adminEmail'] = '';
	$config['Default_TimeZone'] = 'Australia/Sydney';

	$config['max_flash_upload_size'] = '2147483648'; // 2GB
	$config['max_gears_upload_size'] = '100000000000'; // 100 GB
	
	// update max_flash_upload_size if php.ini post_max_size and upload_max_filesize is set lower
	$config['max_flash_upload_size'] = min(let_to_num(ini_get('post_max_size'))-2048, let_to_num(ini_get('upload_max_filesize')),$config['max_flash_upload_size']);
	
	$config['available_space'] = '20000M';
	
	// site URLS, only set these when run as web-app
	if ( isset($_SERVER['SERVER_NAME']) ) {
	$prot =  isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$config['site_url'] = $prot . $_SERVER['SERVER_NAME'] . '/filesender/'; // URL to Filesender
	$config['site_simplesamlurl'] =  $prot . $_SERVER['SERVER_NAME'] . '/simplesaml/';
	$config['site_downloadurl'] = $config['site_url'] . 'files/';
	$config['site_logouturl'] = $config['site_url'] . 'logout.php';
	}

    // (absolute) file locations
	$config['site_filestore'] = '/usr/share/filesender/files/'; 
	$config['site_temp_filestore'] = '/usr/share/filesender/tmp/'; 
	$config['site_simplesamllocation'] = '/usr/share/simplesamlphp/';
	$config['log_location'] = '/usr/share/filesender/log/';	

    // site settings
	$config['site_authenticationSource'] ="default-sp";
	$config['site_defaultlanguage'] = 'EN_AU';
	$config['site_name'] = 'FileSender';
	$config['site_icon'] = 'cloudstor.png';
	$config['site_css'] = '';
	$config['forceSSL'] = true;

	$config['default_daysvalid'] = 20;
	$config['gearsURL'] = 'http://tools.google.com/gears/';

	// database settings	
	$config['pg_host'] = 'localhost';
	$config['pg_database'] = 'filesender';
	$config['pg_port'] = '5432';
	$config['pg_username'] = 'postgres';
	$config['pg_password'] = 'yoursecretpassword';

	// cron settings
	$config['cron_exclude prefix'] = '_'; // exclude deletion of files with the prefix character listed (can use multiple characters eg '._' will ignore .xxxx and _xxxx
	
	// email
	$config['default_emailsubject'] = "{siteName}: {filename}";
	$config['filedownloadedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

The file below has been downloaded from {siteName} by {filefrom}.

Filename: {fileoriginalname}
Filesize: {filesize}
Download link: {serverURL}?vid={filevoucheruid}

The file is available until {fileexpirydate} after which it will be automatically deleted.

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html;charset={charset}">
</HEAD>
<BODY>
<P>Dear Sir, Madam,</P>
<P>The file below has been downloaded from {siteName} by {filefrom}.</P>
<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=600>
	<COL WIDTH=80>
	<COL WIDTH=800>
	<COL WIDTH=70>
	<TR>
		<TD WIDTH=600 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Filename</B></P>
		</TD>
		<TD WIDTH=80 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Filesize</B></P>
		</TD>
		<TD WIDTH=600 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Download link</B></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Valid until</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=600 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{fileoriginalname}</P>
		</TD>
		<TD WIDTH=80 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{filesize}</P>
		</TD>
		<TD WIDTH=800 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER><A HREF="{serverURL}?vid={filevoucheruid}">{serverURL}?vid={filevoucheruid}</A></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{fileexpirydate}</P>
		</TD>
	</TR>
</TABLE>
<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--';
	$config['fileuploadedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

The file below has been uploaded to {siteName} by {filefrom} and you have been granted permission to download this file.

Filename: {fileoriginalname}
Filesize: {filesize}
Download link: {serverURL}?vid={filevoucheruid}

The file is available until {fileexpirydate} after which it will be automatically deleted.

Personal message from {filefrom} (optional): {filemessage}

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html;charset={charset}">
</HEAD>
<BODY>
<P>Dear Sir, Madam,</P>
<P>The file below has been uploaded to {siteName} by {filefrom} and you have been granted permission to download this file.</P>
<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=600>
	<COL WIDTH=80>
	<COL WIDTH=800>
	<COL WIDTH=70>
	<TR>
		<TD WIDTH=600 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Filename</B></P>
		</TD>
		<TD WIDTH=80 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Filesize</B></P>
		</TD>
		<TD WIDTH=600 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Download link</B></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Valid until</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=600 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{fileoriginalname}</P>
		</TD>
		<TD WIDTH=80 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{filesize}</P>
		</TD>
		<TD WIDTH=800 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER><A HREF="{serverURL}?vid={filevoucheruid}">{serverURL}?vid={filevoucheruid}</A></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{fileexpirydate}</P>
		</TD>
	</TR>
</TABLE>
<P></P>
<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=100%>
	<TR>
		<TD WIDTH=100% BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Personal message from {filefrom} (optional):</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=100% BGCOLOR="#e6e6e6">
			<P><I>{htmlfilemessage}</I></P>
		</TD>
	</TR>
</TABLE>
<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--';
	$config['voucherissuedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

Please, find below a voucher which grants access to {siteName}.
With this voucher you can upload once one file and make it available for download to a group of people.

Issuer: {filefrom}
Voucher link: {serverURL}?vid={filevoucheruid}

The voucher is available until {fileexpirydate} after which it will be automatically deleted.

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html;charset={charset}">
</HEAD>
<BODY>
<P>Dear Sir, Madam,</P>
<P>Please, find below a voucher which grants access to {siteName}.</P>
<P>With this voucher you can upload once one file and make it available for download to a group of people.</P>
<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=75>
	<COL WIDTH=800>
	<COL WIDTH=70>
	<TR>
		<TD WIDTH=75 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Issuer</B></P>
		</TD>
		<TD WIDTH=800 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Voucher link</B></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Valid until</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=75 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{filefrom}</P>
		</TD>
		<TD WIDTH=800 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER><A HREF="{serverURL}?vid={filevoucheruid}">{serverURL}?vid={filevoucheruid}</A></P>
		</TD>
		<TD WIDTH=70 BGCOLOR="#e6e6e6">
			<P ALIGN=CENTER>{fileexpirydate}</P>
		</TD>
	</TR>
</TABLE>
<P></P>
<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--';

$config['defaultvouchercancelled'] = "{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

A voucher from {filefrom} has been cancelled.

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<HEAD>
<meta http-equiv=\"Content-Type\" content=\"text/html;charset={charset}\">
</HEAD>
<BODY>
Dear Sir, Madam,<BR><BR>A voucher from {filefrom} has been cancelled.<BR><BR>
	<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--";

$config['defaultfilecancelled'] = "{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

The file '{filename}' from {filefrom} has been cancelled and is no longer available to download.

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<BODY>
Dear Sir, Madam,<BR><BR>The file '{filename}' from {filefrom} has been cancelled and is no longer available to download.<BR><BR>
	<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--";

	$config['site_sendfileinstructions'] = '<B>To send a file.</B><BR>Type an email address into the To: box<BR>Select BROWSE to choose a file on your computer.<BR>Select SEND FILE to upload and send the file.';
	$config['site_voucherinstructions'] = 'A Voucher allows someone to send you a file.<BR>To create a voucher. Enter an email address then select Send Voucher.<BR>An email will be sent to the recipient with a link to use the Voucher.';
	$config['site_downloadinstructions'] = 'A file is available for you.<BR>Select Download File to download the file to your computer.';
	

	
	return $config;
	}
	}

function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $ret = trim($v);
    $last = strtoupper($ret[strlen($ret)-1]);
    switch($last) {
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
      return $ret;
}	
	?>
