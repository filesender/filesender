<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Zusammenfassung der täglichen Dateiübertragungen

{alternative:plain}

Sehr geehrte Damen und Herren,

hier finden Sie eine Zusammenfassung der Downloads Ihrer Dateiübertragung Nr. {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - Empfänger {event.who} hat heruntergeladen {if:event.what == "archive"}archive{else}file {event.what_name}{endif} am {datetime:event.when}
{endeach}
{else}
Keine Downloads
{endif}

Weitere Details finden Sie unter {transfer.link}

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    hier finden Sie eine Zusammenfassung der Downloads Ihrer Dateiübertragung Nr. {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Empfänger {event.who} hat heruntergeladen {if:event.what == "archive"}archive{else}file {event.what_name}{endif} am {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Keine Downloads
</p>
{endif}

<p>
    Weitere Details finden Sie unter <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Mit freundlichen Grüßen<br />
    {cfg:site_name}
</p>
