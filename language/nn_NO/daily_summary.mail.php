<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Dagleg samandrag av overføringa

{alternative:plain}

Hei,

Nedanfor finn du eit samandrag av nedlastingane frå overføringa {transfer.id} (lasta opp {date:transfer.created}):

{if:events}
{each:events as event}
  - {event.who} lasta ned {if:event.what == "archive"}arkivet{else}fila {event.what_name}{endif} {datetime:event.when}
{endeach}
{else}
Ingen nedlastingar
{endif}

Du finn fleire detaljar på {transfer.link}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Nedanfor finn du eit samandrag av nedlastingane frå overføringa {transfer.id} (lasta opp {date:transfer.created}):
</p>

{if:events}
<ul>
{each:events as event}
    <li>{event.who} lasta ned {if:event.what == "archive"}arkivet{else}fila {event.what_name}{endif} {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Ingen nedlastingar
</p>
{endif}

<p>
    Du finn fleire detaljar på <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
