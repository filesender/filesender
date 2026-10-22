<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: automatiske påminnelser sendt for overføring nr. {transfer.id}

{alternative:plain}

Hei,

En automatisk påminnelse ble sendt til mottakerne som ikke lastet ned filer fra overføringen din nr. {transfer.id} på {cfg:site_name} ({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    En automatisk påminnelse ble sendt til mottakerne som ikke lastet ned filer fra <a href="{transfer.link}">overføringen nr. {transfer.id}</a> på <a href="{cfg:site_url}">{cfg:site_name}</a>:
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
