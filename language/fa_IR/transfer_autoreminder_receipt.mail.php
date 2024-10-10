<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automatic reminders sent for file shipment n°{transfer.id}

{alternative:plain}

Dear Sir or Madam,

An automatic reminder was sent to recipients that did not download files from your transfer n°{transfer.id} on {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    An automatic reminder was sent to recipients that did not download files from your <a href="{transfer.link}">transfer n°{transfer.id}</a> on <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
