subject: {siteName}: Download Complete - {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

Your download consisting of the following file(s) has finished.
Tracking code: {filetrackingcode}

Files:
{fileinfo}

Best regards,
{siteName}

{alternative:html}

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
