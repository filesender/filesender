subject: {siteName}: Download receipt - {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

One or more of your uploaded files have been downloaded from {siteName} by {filefrom}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Tracking code: {filetrackingcode}

Files:
{fileinfo}
The transaction will remain available until {fileexpirydate}, after which time it will be automatically deleted.

Best regards,
{siteName}

{alternative:html}

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
