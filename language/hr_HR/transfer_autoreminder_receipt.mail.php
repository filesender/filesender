<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automatski podsjetnik za prijenos n°{transfer.id}
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Automatski podsjetnik poslan je primateljima koji nisu preuzeli datoteke s vašeg prijenosa n°{transfer.id} na {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Automatski podsjetnik poslan je primateljima koji nisu preuzeli datoteke s vašeg <a href="{transfer.link}">prijenosa n°{transfer.id}</a> na <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>
