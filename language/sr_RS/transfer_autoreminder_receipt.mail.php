<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automatski podsetnik za transfer n°{transfer.id}
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Automatski podsetnik poslat je primaocima koji nisu preuzeli fajlove sa vašeg transfera n°{transfer.id} na {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Automatski podsetnik poslat je primaocima koji nisu preuzeli fajlove sa vašeg <a href="{transfer.link}">transfera n°{transfer.id}</a> na <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>
