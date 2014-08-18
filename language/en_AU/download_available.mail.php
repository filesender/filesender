subject: {siteName}: File(s) available for download - {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

The following file transaction has been uploaded to {siteName} by {filefrom} and you have been granted permission to download its contents.

Download link: {serverURL}?gid={filegroupid}

Files:
{fileinfo}
The transaction is available until {fileexpirydate} after which time it will be automatically deleted.
{filemessage_start}
Personal message from {filefrom}: {filemessage}
{filemessage_end}
Best regards,
{siteName}

{alternative:html}

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
