<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Resumen diario de transferencias

{alternative:plain}

Estimado señor o señora,

A continuación encontrará un resumen de las descargas de su transferencia {transfer.id} (subida el {date:transfer.created}) :

{if:events}
{each:events as event}
  - Destinatario {event.who} ha descargado {if:event.what == "archive"}un archivo{else}el archivo {event.what_name}{endif} el {datetime:event.when}
{endeach}
{else}
No hay descargas
{endif}

Puede encontrar más detalles en {transfer.link}

Un cordial saludo,
{cfg:site_name}

{alternative:html}

<p>
    Estimado señor o señora,
</p>

<p>
    A continuación encontrará un resumen de las descargas de su transferencia {transfer.id} (subida el {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Destinatario {event.who} ha descargado {if:event.what == "archive"}un archivo{else}el archivo {event.what_name}{endif} el {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    No hay descargas
</p>
{endif}

<p>
    Puede encontrar más detalles en <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Un cordial saludo,<br />
    {cfg:site_name}
</p>