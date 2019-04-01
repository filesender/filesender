<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Riepilogo giornaliero dei trasferimenti

{alternative:plain}

Gentile utente,

Di seguito trovi un riepilogo dei download per il tuo trasferimento {transfer.id} (caricato il {date:transfer.created}) :

{if:events}
{each:events as event}
  - Il destinatario {event.who} ha scaricato {if:event.what == "archive"}l'archivio {else}il file {event.what_name}{endif} il {datetime:event.when}
{endeach}
{else}
Nessun download
{endif}

Puoi trovare ulteriori dettagli su {transfer.link}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Di seguito trovi un riepilogo dei download per il tuo trasferimento {transfer.id} (caricato il {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Il destinatario {event.who} ha scaricato {if:event.what == "archive"}l'archivio {else}il file {event.what_name}{endif} il {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Nessun download
</p>
{endif}

<p>
    Puoi trovare ulteriori dettagli su <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

