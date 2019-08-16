<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automatische Erinnerung zu einer Dateiübertragung (Nr. {transfer.id})

{alternative:plain}

Sehr geehrte Damen und Herren,

es wurde eine automatische Erinnerung zu einer Dateiübertragung (Nr. {transfer.id}) an den Empfänger gesendet, da diese noch nicht von {cfg:site_name} heruntergeladen wurde.
({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Mit freundliche Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    es wurde eine automatische Erinnerung an den Empfänger gesendet, für die nicht heruntergeladenen Dateien, von Ihrer <a href="{transfer.link}">Dateiübertragung Nr. {transfer.id}</a> auf <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Mit freundliche Grüßen,<br />
    {cfg:site_name}
</p>