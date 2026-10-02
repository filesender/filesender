<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Failijagamise päevane kokkuvõte

{alternative:plain}

Tere,

Allpool on kirjas failijagamise kokkuvõte:  {transfer.id} (üleslaetud {date:transfer.created}) :

{if:events}
{each:events as event}
  - Saaja {event.who} laadis alla {if:event.what == "archive"}arhiivi{else}faili {event.what_name}{endif} kuupäeval {datetime:event.when}
{endeach}
{else}
Allalaadimised puuduvad.
{endif}

Rohkem infot leiad siit {transfer.link}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Allpool on kirjas failijagamise kokkuvõte: {transfer.id} (üleslaetud {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Saaja {event.who} laadis alla {if:event.what == "archive"}arhiivi{else}faili {event.what_name}{endif} kuupäeval {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Allalaadimised puuduvad
</p>
{endif}

<p>
    Rohkem infot leiad siit <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
