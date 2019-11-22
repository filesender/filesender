<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Dnevni povzetek prenosov

{alternative:plain}
Spoštovani,

Povzetek prenosov Vašega nalaganja {transfer.id} (uploaded {date:transfer.created}) najdete spodaj:

{if:events}
{each:events as event}
  - Prejemnik {event.who} je prenesel {if:event.what == "archive"}arhiv{else}datoteko {event.what_name}{endif} v času {datetime:event.when}
{endeach}
{else}
Prenosov ni bilo
{endif}

Podrobnosti lahko najdete na {transfer.link}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Povzetek prenosov Vašega nalaganja {transfer.id} (uploaded {date:transfer.created}) najdete spodaj:
</p>

{if:events}
<ul>
{each:events as event}
    <li>Prejemnik {event.who} je prenesel {if:event.what == "archive"}arhiv{else}datoteko {event.what_name}{endif} v času {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Prenosov ni bilo
</p>
{endif}

<p>
    Podrobnosti lahko najdete na <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>