<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automatiske påminningar er sende for overføring nr. {transfer.id}

{alternative:plain}

Hei,

Ei automatisk påminning vart sendt til mottakarane som ikkje lasta ned filer frå overføringa di nr. {transfer.id} på {cfg:site_name} ({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Ei automatisk påminning vart sendt til mottakarane som ikkje lasta ned filer frå <a href="{transfer.link}">overføringa nr. {transfer.id}</a> på <a href="{cfg:site_url}">{cfg:site_name}</a>:
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
