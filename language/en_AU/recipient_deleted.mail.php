subject: {siteName}: Recipient deleted - {filetrackingcode}

{alternative:plain}

Dear Sir or Madam,

Recipient {recemail} has been removed from your file shipment on {siteName} with tracking code {filetrackingcode}. You can access your files and view detailed download statistics on the My Files page at {serverURL}?s=files.

Best regards,
{siteName}

{alternative:html}

<p>Dear Sir or Madam,</p>
<p>Recipient <a href="mailto:{recemail}">{recemail}</a> has been removed from your file shipment on <a href="{serverURL}">{siteName}</a> with tracking code {filetrackingcode}. You can access your files and view detailed download statistics on the <a href="{serverURL}?s=files">My Files</a> page.</p>
<p>Best regards,<br />
{siteName}</p>
