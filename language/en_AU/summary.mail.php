subject: {siteName}: Daily summary for transaction {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

This is a daily transaction activity summary for your file shipment on {siteName}. You can access your files and view detailed download statistics on the My Files page.

Activity:
{transactionactivity}
Best regards,
{siteName}

{alternative:html}

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
