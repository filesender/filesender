<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Wysłano automatyczne przypomnienie wysłane dla transferu nr {transfer.id}
subject: (Wysłano automatyczne przypomnienie wysłane) {transfer.subject}

{alternative:plain}

Szanowni Państwo,

Automatyczne przypomnienie zostało wysłane do odbiorców, którzy nie pobrali jeszcze plików z transferu nr {transfer.id} na {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    utomatyczne przypomnienie zostało wysłane do odbiorców, którzy nie pobrali jeszcze plików z <a href="{transfer.link}">transferu nr {transfer.id}</a> na <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

