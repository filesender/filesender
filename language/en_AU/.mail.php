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

// Email subjects.
$lang['_EMAIL_SUBJECT_DEFAULT'] = '{siteName}: {filetrackingcode}';
$lang['_EMAIL_SUBJECT_SUMMARY'] = '{siteName}: Daily summary for transaction {filetrackingcode}';
$lang['_EMAIL_SUBJECT_FILES_DOWNLOADED'] = '{siteName}: Download receipt - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_DOWNLOAD_COMPLETE'] = '{siteName}: Download Complete - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_FILES_UPLOADED'] = '{siteName}: File(s) uploaded - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_DOWNLOAD_AVAILABLE'] = '{siteName}: File(s) available for download - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_RECIPIENT_DELETED'] = '{siteName}: Recipient deleted - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_TRANSACTION_DELETED'] = '{siteName}: File(s) deleted - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_TRANSACTION_NO_LONGER_AVAILABLE'] = '{siteName}: File(s) no longer available for download - {filetrackingcode}';
$lang['_EMAIL_SUBJECT_VOUCHER'] = 'Voucher';
$lang['_EMAIL_SUBJECT_VOUCHER_ISSUED'] = '{siteName}: Voucher received';
$lang['_EMAIL_SUBJECT_VOUCHER_ISSUED_RECEIPT'] = '{siteName}: Voucher sent';
$lang['_EMAIL_SUBJECT_VOUCHER_CANCELLED'] = '{siteName}: Voucher cancelled';
$lang['_EMAIL_SUBJECT_BOUNCE'] = '{siteName}: Email notification sending failure';

// Email bodies.
$lang['_EMAIL_BODY_SUMMARY'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

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
        <p>Dear Sir or Madam,</p>
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

$lang['_EMAIL_BODY_FILES_DOWNLOADED'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

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
        <p>Dear Sir or Madam,</p>
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

$lang['_EMAIL_BODY_FILES_DOWNLOAD_COMPLETE'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

Your download consisting of the following file(s) has finished.
Tracking code: {filetrackingcode}

Files:
{fileinfo}

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body bgcolor="#ffffff">
        <p>Dear Sir or Madam,</p>
        <p>Your download consisting of the following file(s) has finished.</p>
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

$lang['_EMAIL_BODY_FILES_UPLOADED'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

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
        <p>Dear Sir or Madam,</p>
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

$lang['_EMAIL_BODY_DOWNLOAD_AVAILABLE'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

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
        <p>Dear Sir or Madam,</p>
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
        {filemessage_start}<p>Personal message from {filefrom}: {htmlfilemessage}</p>{filemessage_end}
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_RECIPIENT_DELETED'] =  '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

Recipient {recemail} has been removed from your file shipment on {siteName} with tracking code {filetrackingcode}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body bgcolor="#ffffff">
        <p>Dear Sir or Madam,</p>
        <p>Recipient <a href="mailto:{recemail}">{recemail}</a> has been removed from your file shipment on <a href="{serverURL}">{siteName}</a> with tracking code {filetrackingcode}. You can access your files and view detailed download statistics on the <a href="{serverURL}?s=files">My Files</a> page.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_TRANSACTION_DELETED'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

Your file shipment with tracking code {filetrackingcode} has been deleted from {siteName} and is no longer available for download.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body bgcolor="#ffffff">
        <p>Dear Sir or Madam,</p>
        <p>Your file shipment with tracking code {filetrackingcode} has been deleted from <a href="{serverURL}">{siteName}</a> and is no longer available for download.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_TRANSACTION_NO_LONGER_AVAILABLE'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

The file shipment with tracking code {filetrackingcode} has been deleted from {siteName} by the sender ({filefrom}) and is no longer available for download.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body bgcolor="#ffffff">
        <p>Dear Sir or Madam,</p>
        <p>The file shipment with tracking code {filetrackingcode} has been deleted from <a href="{serverURL}">{siteName}</a> by the sender (<a href="mailto:{filefrom}">{filefrom}</a>) and is no longer available for download.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_VOUCHER_ISSUED'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

Please find below a voucher which grants access to {siteName}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {filefrom}
Voucher link: {serverURL}?vid={filevoucheruid}

The voucher is available until {fileexpirydate} after which time it will be automatically deleted.
{filemessage_start}
Personal message from {filefrom}: {filemessage}
{filemessage_end}
Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body>
        <p>Dear Sir or Madam,</p>
        <p>Please find below a voucher which grants access to <a href="{serverURL}">{siteName}</a>. You can use this voucher to upload one set of files and make it available for download to a group of people.</p>
        <table width="960" cellspacing="0" cellpadding="3" border="1" bordercolor="#bbbbbb" rules="rows">
            <tbody>
            <tr bgcolor="#cccccc">
                <td colspan="2" height="30"><strong>Voucher details</strong></td>
            </tr>
            <tr bgcolor="#e5e5e5" valign="top">
                <td width="100"><strong>Issuer</strong></td>
                <td><a href="mailto:{filefrom}">{filefrom}</a></td>
            </tr>
            <tr valign="top">
                <td width="100"><strong>Voucher link</strong></td>
                <td><a href="{serverURL}?vid={filevoucheruid}">{serverURL}?vid={filevoucheruid}</a></td>
            </tr>
            <tr bgcolor="#e5e5e5" valign="top">
                <td width="100"><strong>Valid until</strong></td>
                <td>{fileexpirydate}</td>
            </tr>
            </tbody>
        </table>
        {filemessage_start}<p>Personal message from {filefrom}: {filemessage}</p>{filemessage_end}
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_VOUCHER_ISSUED_RECEIPT'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

A guest voucher granting access to {siteName} has been sent to {recemail}.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body>
        <p>Dear Sir or Madam,</p>
        <p>A guest voucher granting access to <a href="{serverURL}">{siteName}</a> has been sent to <a href="mailto:{recemail}">{recemail}</a>.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_VOUCHER_CANCELLED'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

A guest voucher from {filefrom} has been cancelled.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body>
        <p>Dear Sir or Madam,</p>
        <p>A guest voucher from <a href="mailto:{filefrom}">{filefrom}</a> has been cancelled.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';

$lang['_EMAIL_BODY_VOUCHER_CANCELLED_RECEIPT'] = '{CRLF}--simple_mime_boundary{CRLF}Content-type:text/plain; charset={charset}{CRLF}{CRLF}Dear Sir or Madam,

A guest voucher that was sent to {recemail} has been cancelled.

Best regards,
{siteName}{CRLF}{CRLF}--simple_mime_boundary{CRLF}Content-type:text/html; charset={charset}{CRLF}{CRLF}
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset={charset}">
    </head>
    <body>
        <p>Dear Sir or Madam,</p>
        <p>A guest voucher that was sent to <a href="mailto:{recemail}">{recemail}</a> has been cancelled.</p>
        <p>Best regards,<br />
        {siteName}</p>
    </body>
</html>{CRLF}{CRLF}--simple_mime_boundary--';
