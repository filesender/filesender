onderwerp: dagstaat van uw transfers

{alternative:plain}

Geachte Mevrouw, Heer,

Onderstaand gelieve aan te treffen een dagstaat van de downloads van uw transfer {transfer.id} (ge-upload op {date:transfer.created}) :

{if:events} {each:events as event}
 - Ontvanger {event.who} heeft gedownload {if:event.what == "archive"}archief{else}bestand{event.what_name}{endif} op {datetime:event.when}
{endeach}
{else}
Geen downloads
{endif}

U kunt nadere details bekijken op {transfer.link}
Hoogachtend,{cfg:site_name}

{alternative:html}

<p>
    DGeachte Mevrouw, Heer,
</p>

<p>
    Onderstaand gelieve aan te treffen een dagstaat van de downloads van uw transfer {transfer.id} (ge-upload op {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Ontvanger {event.who} heeft gedownload {if:event.what == "archive"}archief{else}bestand {event.what_name}{endif} op {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Geen dowloads
</p>
{endif}

<p>
    U kunt nadere details bekijken op <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>