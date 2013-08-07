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

	// Start of configurable settings
	// For more information about these settings please see the
	// Administrator Reference Manual in the documentation section
	// at www.filesender.org

	// General settings
	$config['admin'] = ''; // UID's (from $config['saml_uid_attribute']) that have Administrator permissions
	$config['adminEmail'] = ''; // Email address(es, separated by ,) to receive administrative messages (low disk space warning)
	$config['Default_TimeZone'] = 'Australia/Sydney';
	$config['site_defaultlanguage'] = 'en_AU'; // for available languages see the ./language directory
	$config['site_name'] = 'FileSender'; // Friendly name used for your FileSender instance
	$config['noreply'] = 'noreply@filesender.org'; // default no-reply email address 

	// UI Settings
	$config['datedisplayformat'] = "d-m-Y"; // Format for displaying date/time, use PHP date() format string syntax
	$config["versionNumber"] = true; // Show version number (true/false)
	$config['site_showStats'] = false; // Show site upload/download stats (true/false)
    
	// auto complete - provides auto complete in input field for emails
	$config["autocomplete"] = true;
	$config["autocompleteHistoryMax"] = ""; // "" - unlimited or integer, number of results displayed in autocomplete

	// debug settings
	$config["debug"] = false; // Debug logging on/off (true/false)
	$config["displayerrors"] = false; // Display debug errors on screen (true/false)
	$config['dnslookup'] = true; // log includes DNS lookup (true/false)
	$config["client_specific_logging"] = false; // client logging (true/false)
	$config["client_specific_logging_uids"] = ""; // "" is log all clients, or log for specific userid's or voucheruid's seperated by comma 'xxxx,zzzzz'

	// saml settings
	$config['saml_email_attribute'] = 'mail'; // Attribute used for email address
	$config['saml_name_attribute'] = 'cn'; // Attribute used to get the user's name
	$config['saml_uid_attribute'] = 'eduPersonTargetedID'; // Attribute to uniquely identify the user

	// AuP settings
	$config["AuP_default"] = false; //AuP value is already ticked
	$config["AuP"] = true; // AuP is displayed

	// Server settings
	$config['default_daysvalid'] = 20; // Maximum number of days before file/voucher is expired
	$config['ban_extension'] = 'exe,bat'; // Possibly dangerous file extensions that are disallowed
	$config["max_email_recipients"] = 100; // maximum email addresses allowed to send at once for voucher or file sending, a value of 0 allows unlimited emails.

	$config['max_flash_upload_size'] = '2147483648'; // 2GB
	$config['max_html5_upload_size'] = '107374182400'; // 100  GB
	$config["upload_chunk_size"]  = '2000000';//
    $config["download_chunk_size"] = '5242880'; // The maximum amount of data that will be read into memory at once during multi-file downloads, default 5MB.
    $config["html5_max_uploads"] = 30; // Max number of simultaneous uploads.

	// update max_flash_upload_size if php.ini post_max_size and upload_max_filesize is set lower
	$config['max_flash_upload_size'] = min(let_to_num(ini_get('post_max_size'))-2048, let_to_num(ini_get('upload_max_filesize')),$config['max_flash_upload_size']);

	$config["server_drivespace_warning"] = 20; // as a percentage 20 = 20% space left on the storage drive

	// Terasender (fast upload) settings
	// - terasender (really fast uploads) uses html5 web workers to speed up file upload
	// - effectively providing multi-threaded faster uploads
	$config['terasender'] = true; // true/false
	$config['terasenderadvanced'] = false; // true/false - terasender advanced - show advanced settings
	$config['terasender_chunksize'] = 5;		// default (5) terasender chunk size in MB
	$config['terasender_workerCount'] = 6;		// default (6) worker count
	$config['terasender_jobsPerWorker'] = 1;	// default (1) jobs per worker

    // Email flow settings
    // Can be either 'always' 'hidden' or 'off'
    // settings marked as 'always' will be displayed in the right hand column of the upload page
    // settings marked as 'hidden' will be contained in 'More options' in the right hand column of the upload page
    // settings marked as 'off' are completely disabled and are not displayed anywhere
    // true/false on default fields specify whether or not boxes are checked on page load
    $config['email_me_copies_display'] = 'always';
    $config['email_me_copies_default'] = false;

    $config['upload_complete_email_display'] = 'always';
    $config['upload_complete_email_default'] = true;

    $config['inform_download_email_display'] = 'hidden';
    $config['inform_download_email_default'] = true;

    $config['email_me_daily_statistics_display'] = 'always';
    $config['email_me_daily_statistics_default'] = false;

    $config['download_confirmation_enabled_display'] = 'hidden';
    $config['download_confirmation_enabled_default'] = true;

    $config['email_only_me_display'] = 'hidden';
    $config['email_only_me_default'] = false;

	// Advanced server settings, do not change unless you have a very good reason.
	$config['db_dateformat'] = "Y-m-d H:i:sP"; // Date/Time format for PostgreSQL, use PHP date format specifier syntax
	$config["crlf"] = "\n"; // for email CRLF can be changed to \r\n if required
	$config['voucherRegEx'] = "'[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}'";
	$config['voucherUIDLength'] = 36;
    $config['openSSLKeyLength'] = 30;
	$config['emailRegEx'] = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
    $config['webWorkersLimit'] = 16; // The highest number of web workers that is supported by all modern browsers (currently constrained by Opera).

	// site URL settings
	if ( isset($_SERVER['SERVER_NAME']) ) {
	$prot =  isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$config['site_url'] = $prot . $_SERVER['SERVER_NAME'] . '/filesender/'; // URL to Filesender
	$config['site_simplesamlurl'] =  $prot . $_SERVER['SERVER_NAME'] . '/simplesaml/';
	$config['site_authenticationSource'] ="default-sp";
	$config['site_logouturl'] = $config['site_url'] . '?s=logout';
	}
	$config['forceSSL'] = true; // Always use SSL (true/false)

	// Support links
	$config['aboutURL'] = "";
	$config['helpURL'] = "";

	// (absolute) file locations
	$config['site_filestore'] = '/usr/share/filesender/files/';
	$config['site_temp_filestore'] = '/usr/share/filesender/tmp/';
	$config['site_simplesamllocation'] = '/usr/share/simplesamlphp/';
	$config['log_location'] = '/usr/share/filesender/log/';

	$config["db_type"] = "pgsql";// pgsql or mysql
	$config['db_host'] = 'localhost';
	$config['db_database'] = 'filesender';
	$config['db_port'] = '5432';
	// database username and password
	$config['db_username'] = 'filesender';
	$config['db_password'] = 'yoursecretpassword';

	//Optional DSN format overides db_ settings
	//$config['dsn'] = "pgsql:host=localhost;dbname=filesender";
	//$config['dsn'] = 'pgsql:host=localhost;dbname=filesender';
	//$config['dsn'] = 'sqlite:/usr/share/filesender/db/filesender.sqlite';
	//$config['dsn_driver_options'] = array();
	// dsn requires username and password in $config['db_username'] and $config['db_password']

	// cron settings
	$config['cron_exclude prefix'] = '_'; // exclude deletion of files with the prefix character listed (can use multiple characters eg '._' will ignore .xxxx and _xxxx
	$config['cron_shred'] = false; // instead of simply unlinking, overwrite expired files so they are hard to recover
	$config['cron_shred_command'] = '/usr/bin/shred -f -u -n 1 -z'; // overwrite once (-n 1) with random data, once with zeros (-z), then remove (-u)
	$config["cron_cleanuptempdays"] = 7; // number of days to keep temporary files in the temp_filestore

	// email templates section
	$config['default_emailsubject'] = "{siteName}: {filetrackingcode}";
    $config['summary_email_subject'] = "{siteName}: Daily summary for transaction {filetrackingcode}";

    $config['summaryemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir, Madam,

This is a daily transaction activity summary for your file shipment on {siteName}. You can access your files and view detailed download statistics on the My Files page.

Activity:
{transactionactivity}
Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>This is a daily transaction activity summary for your file shipment on {siteName}. You can access your files and view detailed download statistics on the My Files page.</p>
		<table width="960" cellspacing="0" cellpadding="3" border="1" bordercolor="#bbbbbb" rules="rows">
			<tbody>
				<tr bgcolor="#cccccc">
					<td colspan="2" height="30"><strong>Transaction details</strong></td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Tracking code</strong></td>
					<td>{filetrackingcode}</td>
				</tr>
				<tr valign="top">
					<td width="100"><strong>Activity</strong></td>
					<td>{htmltransactionactivity}</ul>
					</td>
				</tr>
			</tbody>
		</table>
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

	$config['filedownloadedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

One or more of your uploaded files have been downloaded from {siteName} by {filefrom}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Tracking code: {filetrackingcode}

Files:
{fileinfo}
The transaction will remain available until {fileexpirydate}, after which time it will be automatically deleted.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>One or more of your uploaded files have been downloaded from <a href="{serverURL}">{siteName}</a> by <a href="mailto:{filefrom}">{filefrom}</a>. You can access your files and view detailed download statistics on the <a href="{serverURL}?s=files">My Files</a> page.</p>
		<table width="960" cellspacing="0" cellpadding="3" border="1" bordercolor="#bbbbbb" rules="rows">
			<tbody>
				<tr bgcolor="#cccccc">
					<td colspan="2" height="30"><strong>Transaction details</strong></td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Tracking code</strong></td>
					<td>{filetrackingcode}</td>
				</tr>
				<tr valign="top">
					<td width="100"><strong>Expiry date</strong></td>
					<td>{fileexpirydate}</td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Files</strong></td>
					<td>{htmlfileinfo}</td>
				</tr>
			</tbody>
		</table>
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';
    $config['transactionuploadedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir, Madam,

The following file transaction has been successfully uploaded to {siteName}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Files:
{fileinfo}
The transaction has been made available until {fileexpirydate} after which time it will be automatically deleted.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>The following file transaction has been successfully uploaded to <a href="{serverURL}">{siteName}</a>. You can access your files and view detailed download statistics on the <a href="{serverURL}?s=files">My Files</a> page.</p>
		<table width="960" cellspacing="0" cellpadding="3" border="1" bordercolor="#bbbbbb" rules="rows">
			<tbody>
				<tr bgcolor="#cccccc">
					<td colspan="2" height="30"><strong>Transaction details</strong></td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Tracking code</strong></td>
					<td>{filetrackingcode}</td>
				</tr>
				<tr valign="top">
					<td width="100"><strong>Expiry date</strong></td>
					<td>{fileexpirydate}</td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Files</strong></td>
					<td>{htmlfileinfo}</ul>
					</td>
				</tr>
			</tbody>
		</table>
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';
    $config['transactionavailableemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir, Madam,

The following file transaction has been uploaded to {siteName} by {filefrom} and you have been granted permission to download its contents.

Download link: {serverURL}?gid={filegroupid}

Files:
{fileinfo}
The transaction is available until {fileexpirydate} after which time it will be automatically deleted.
{filemessage_start}
Personal message from {filefrom}: {filemessage}
{filemessage_end}
Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>The following file transaction has been uploaded to <a href="{serverURL}">{siteName}</a> by <a href="mailto:{filefrom}">{filefrom}</a> and you have been granted permission to download its contents.</p>
		<table width="960" cellspacing="0" cellpadding="3" border="1" bordercolor="#bbbbbb" rules="rows">
			<tbody>
				<tr bgcolor="#cccccc">
					<td colspan="2" height="30"><strong>Transaction details</strong></td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Tracking code</strong></td>
					<td>{filetrackingcode}</td>
				</tr>
				<tr valign="top">
					<td width="100"><strong>Download link</strong></td>
					<td><a href="{serverURL}?gid={filegroupid}">{serverURL}?gid={filegroupid}</a></td>
				</tr>
				<tr bgcolor="#e5e5e5" valign="top">
					<td width="100"><strong>Expiry date</strong></td>
					<td>{fileexpirydate}</td>
				</tr>
				<tr valign="top">
					<td width="100"><strong>Files</strong></td>
					<td>{htmlfileinfo}</ul>
					</td>
				</tr>
			</tbody>
		</table>
		{filemessage_start}<p>Personal message from {filefrom}: {filemessage}</p>{filemessage_end}
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

    $config['transactiondeletedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir, Madam,

Your file shipment with tracking code {filetrackingcode} has been deleted from {siteName} and is no longer available for download.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>Your file shipment with tracking code {filetrackingcode} has been deleted from <a href="{serverURL}">{siteName}</a> and is no longer available for download.</p>
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

    $config['transactionnolongeravailableemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir, Madam,

The file shipment with tracking code {filetrackingcode} has been deleted from {siteName} by the sender ({filefrom}) and is no longer available for download.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset={charset}">
	</head>
	<body bgcolor="#ffffff">
		<p>Dear Sir or Madam</p>
		<p>The file shipment with tracking code {filetrackingcode} has been deleted from <a href="{serverURL}">{siteName}</a> by the sender ({filefrom}) and is no longer available for download.</p>
		<p>Best regards,<br />
		{siteName}</p>
	</body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

	$config['fileuploadedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

The file below has been uploaded to {siteName} by {filefrom} and you have been granted permission to download this file.

Filename: {fileoriginalname}
Filesize: {filesize}
Download link: {serverURL}?vid={filevoucheruid}

The file is available until {fileexpirydate} after which it will be automatically deleted.

{filemessage_start}Personal message from {filefrom}: {filemessage}{filemessage_end}

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
			<P ALIGN=CENTER>{htmlfileoriginalname}</P>
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
{filemessage_start}<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=100%>
	<TR>
		<TD WIDTH=100% BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Personal message from {filefrom}:</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=100% BGCOLOR="#e6e6e6">
			<P><I>{htmlfilemessage}</I></P>
		</TD>
	</TR>
</TABLE>{filemessage_end}
<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--';

	$config['voucherissuedemailsubject'] = 'Voucher';
	$config['voucherissuedemailbody'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}
Dear Sir, Madam,

Please, find below a voucher which grants access to {siteName}.
With this voucher you can upload once one file and make it available for download to a group of people.

Issuer: {filefrom}
Voucher link: {serverURL}?vid={filevoucheruid}

The voucher is available until {fileexpirydate} after which it will be automatically deleted.

{filemessage_start}Personal message from {filefrom}: {filemessage}{filemessage_end}

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
{filemessage_start}<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=100%>
	<TR>
		<TD WIDTH=100% BGCOLOR="#b3b3b3">
			<P ALIGN=CENTER><B>Personal message from {filefrom}:</B></P>
		</TD>
	</TR>
	<TR>
		<TD WIDTH=100% BGCOLOR="#e6e6e6">
			<P><I>{htmlfilemessage}</I></P>
		</TD>
	</TR>
</TABLE>{filemessage_end}
<p></p>
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

The file '{fileoriginalname}' from {filefrom} has been deleted and is no longer available to download.

Best regards,

{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<HTML>
<BODY>
Dear Sir, Madam,<BR><BR>The file '{htmlfileoriginalname}' from {filefrom} has been deleted and is no longer available to download.<BR><BR>
	<P>Best regards,</P>
<P>{siteName}</P>
</BODY>
</HTML>{CRLF}{CRLF}--simple_mime_boundary--";
	// End of email templates section

	// End of configurable settings

	return $config;
	}
}

// Helper function used when calculating maximum upload size from the various maxsize configuration items
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
