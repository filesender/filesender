subject: Denní souhrn přenosu

{alternative:plain}

Vážený uživateli,

níže naleznete shrnutí aktivity stahování u Vašeho přenosu {transfer.id} (nahráno {date:transfer.created}) :

{if:events}
{each:events as event}
  - Příjemce {event.who} stáhnul {if:event.what == "archive"}archiv{else}soubor {event.what_name}{endif} dne {datetime:event.when}
{endeach}
{else}
Žádná stažení
{endif}

Více informací naleznete na adrese {transfer.link}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    níže naleznete shrnutí aktivity stahování u Vašeho přenosu {transfer.id} (nahráno {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Příjemce {event.who} stáhnul {if:event.what == "archive"}archiv{else}soubor {event.what_name}{endif} dne {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Žádná stažení
</p>
{endif}

<p>
    Více informací naleznete na adrese <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
