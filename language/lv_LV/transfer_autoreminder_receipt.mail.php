<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automātiskie atgādinājumi, kas nosūtīti faila sūtījumam Nr. {transfer.id}

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Automātisks atgādinājums tika nosūtīts adresātiem, kuri nelejupielādēja failus no Jūsu pārsūtīšanas Nr. {transfer.id} no {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Automātisks atgādinājums tika nosūtīts adresātiem, kuri nelejupielādēja failus no Jūsu pārsūtīšanas Nr. <a href="{transfer.link}">transfer n°{transfer.id}</a> no <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>