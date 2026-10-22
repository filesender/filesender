<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Daglig overføringsoppsummering

{alternative:plain}

Hei,

Under finner du en oppsummering av nedlastinger for din overføring #{transfer.id} (opplastet {date:transfer.created}) :

{if:events}
{each:events as event}
  - Mottaker {event.who} lastet ned {if:event.what == "archive"}arkiv{else}fil {event.what_name}{endif} den {datetime:event.when}
{endeach}
{else}
Ingen nedlastinger
{endif}

Flere detaljer er tilgjengelig på {transfer.link}

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Under finner du en oppsummering av nedlastinger for din overføring #{transfer.id} (opplastet {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Mottaker {event.who} lastet ned {if:event.what == "archive"}arkiv{else}fil {event.what_name}{endif} den {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Ingen nedlastinger
</p>
{endif}

<p>
    Flere detaljer er tilgjengelig påt <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Med hilsen,<br />
    {cfg:site_name}
</p>
