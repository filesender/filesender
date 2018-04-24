Betreff: Automatische Erinnerung für eine Datei Sendung n°{transfer.id}

{alternative:plain}

Sehr geehrte Damen und Herren,

es wurde eine automatische Erinnerung an den Empfänger gesendet, für die nicht heruntergeladenen Dateien, von Ihrer Übertragung  n°{transfer.id} an {cfg:site_name} ({transfer.link}) :

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
    es wurde eine automatische Erinnerung an den Empfänger gesendet, für die nicht heruntergeladenen Dateien, von Ihrer Übertragung  <a href="{transfer.link}">transfer n°{transfer.id}</a> an <a href="{cfg:site_url}">{cfg:site_name}</a> :
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