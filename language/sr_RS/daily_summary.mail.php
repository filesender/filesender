<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Dnevni pregled

{alternative:plain}

Poštovani,

U nastavku pogledajte pregled preuzimanja za vaš transfer {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - Primalac {event.who} preuzeo {if:event.what == "archive"}arhivu{else}fajl {event.what_name}{endif} dana {datetime:event.when}
{endeach}
{else}
Nema preuzimanja
{endif}

Dodatne pojedinosti možete naći na  {transfer.link}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    U nastavku pogledajte pregled preuzimanja za vaš transfer {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Primalac {event.who} preuzeo {if:event.what == "archive"}arhivu{else}fajl {event.what_name}{endif} dana {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Nema preuzimanja
</p>
{endif}

<p>
    Dodatne pojedinosti možete naći na <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>