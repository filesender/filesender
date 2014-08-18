subject: {siteName}: File(s) uploaded - {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

The following file transaction has been successfully uploaded to {siteName}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Files:
{fileinfo}
The transaction has been made available until {fileexpirydate} after which time it will be automatically deleted.

Best regards,
{siteName}

{alternative:html}

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
