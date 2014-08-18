subject: {siteName}: Voucher received

{alternative:plain}

Dear Sir or Madam,

Please find below a voucher which grants access to {siteName}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {filefrom}
Voucher link: {serverURL}?vid={filevoucheruid}

The voucher is available until {fileexpirydate} after which time it will be automatically deleted.
{filemessage_start}
Personal message from {filefrom}: {filemessage}
{filemessage_end}
Best regards,
{siteName}

{alternative:html}

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
