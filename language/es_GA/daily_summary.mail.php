<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Transfer daily summary

{alternative:plain}

Dear Sir or Madam,

Please find below a summary of downloads for your transfer {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}
{endeach}
{else}
No downloads
{endif}

You may find additionnal details at {transfer.link}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Please find below a summary of downloads for your transfer {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    No downloads
</p>
{endif}

<p>
    You may find additionnal details at <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
