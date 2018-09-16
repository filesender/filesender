onderwerp: automatische herinneringen verzonden voor bestandsverzending n° {transfer.id}

{alternative:plain}

Geachte mevrouw, heer,

Er is een automatische herinnering verzonden naar ontvangers die geen bestanden hebben gedownload van uw transfer n° {transfer.id} op {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
    Er is een automatische herinnering verzonden naar ontvangers die geen bestanden hebben gedownload van uw <a href="{transfer.link}">transfer n°{transfer.id}</a> on <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>

