Betreff: Zusammenfassung der täglichen Übertragungen

{alternative:plain}

Sehr geehrte Damen und Herren,

hier finden Sie eine Zusammenfassung der Downloads Ihrer Übertragungen  {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - Empfänger {event.who} hat heruntergeladen {if:event.what == "archive"}archive{else}file {event.what_name}{endif} am {datetime:event.when}
{endeach}
{else}
Keine Downlads
{endif}

Weitere Details finden Sie unter {transfer.link}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    hier finden Sie eine Zusammenfassung der Downloads Ihrer Übertragungen  {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Empfänger {event.who} hat heruntergeladen {if:event.what == "archive"}archive{else}file {event.what_name}{endif} am {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Keine Downlaods
</p>
{endif}

<p>
    Weitere Details finden Sie unter <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Mit freundlichen Grüßen, <br />
    {cfg:site_name}
</p>
