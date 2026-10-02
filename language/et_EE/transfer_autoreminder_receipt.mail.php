<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Failijagamise n°{transfer.id} automaatne meeldetuletus saadetud
subject: (automaatne meeldetuletus saadetud) {transfer.subject}

{alternative:plain}

Tere,

Failijagamise n°{transfer.id} (link: {transfer.link}) automaatne meeldetuletus on edastatud e-posti aadressidele kes ei ole veel faili allalaadinud saidilt {cfg:site_name}:

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Failijagamise <a href="{transfer.link}">transfer n°{transfer.id}</a> automaatne meeldetuletus on edastatud e-posti aadressidele kes ei ole veel faili alla laadinud saidilt <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
