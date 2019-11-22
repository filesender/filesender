<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Avtomatski opomniki poslani za pošiljko n°{transfer.id}

{alternative:plain}

Spoštovani,

Avtomatski opomnik je bil poslan prejemnikom, ki niso prenesli datotek Vašega nalaganja n°{transfer.id} on {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Avtomatski opomnik je bil poslan prejemnikom, ki niso prenesli datotek Vašega <a href="{transfer.link}">nalaganja n°{transfer.id}</a> on <a href="{cfg:site_url}">{cfg:site_name}</a> :
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