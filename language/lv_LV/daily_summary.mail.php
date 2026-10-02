<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Pārsūtīšanas dienas kopsavilkums

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Lūdzu, zemāk atrodiet kopsavilkumu par lejupielādēm Jūsu pārsūtīšanai {transfer.id} (augšupielādēts {date:transfer.created}) :

{if:events}
{each:events as event}
  - Adresāts {event.who} lejupielādēja {if:event.what == "archive"} arhīva {else} failu {event.what_name}{endif} {datetime:event.when}
{endeach}
{else}
Nav lejupielāžu
{endif}

Papildinformāciju varat atrast vietnē {transfer.link}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Lūdzu, zemāk atrodiet kopsavilkumu par lejupielādēm Jūsu pārsūtīšanai {transfer.id} (augšupielādēts {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Adresāts {event.who} lejuplādēja {if:event.what == "archive"} arhīva {else} failu {event.what_name}{endif} {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Nav lejupielāžu
</p>
{endif}

<p>
    Papildinformāciju varat atrast vietnē <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>