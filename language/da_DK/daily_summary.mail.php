<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Dagens overførsler

{alternative:plain}

Kære afsender!

Her har du en oversigt over hentninger af {transfer.id} (uploadet {date:transfer.created}):

{if:events}
{each:events as event}
  - Modtageren {event.who} hentede {if:event.what == "archive"}arkivet{else}filen {event.what_name}{endif} på {datetime:event.when}
{endeach}
{else}
Ingen hentninger
{endif}

Du kan finde flere detaljer på {transfer.link}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender
</p>

<p>
Her har du en oversigt over hentninger af {transfer.id} (uploadet {date:transfer.created}):
</p>

{if:events}
<ul>
{each:events as event}
    <li>Modtageren {event.who} hentede {if:event.what == "archive"}arkivet{else}filen {event.what_name}{endif} på {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
Ingen hentninger
</p>
{endif}

<p>
    Du kan finde flere detaljer på <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>