<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fallo en la entrega de mensajes

{alternative:plain}

Estimado señor o señora,

Uno o más de sus destinatarios no recibieron su(s) mensaje(s) :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transferencia #{bounce.target.transfer.id} para {bounce.target.email} el {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Invitado {bounce.target.email} el {datetime:bounce.date}
{endif}
{endeach}

Puede encontrar más detalles en {cfg:site_url}

Un cordial saludo,
{cfg:site_name}

{alternative:html}

<p>
    Estimado señor o señora,
</p>

<p>
    Uno o más de sus destinatarios no recibieron su(s) mensaje(s) :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transferencia #{bounce.target.transfer.id}</a> para {bounce.target.email} el {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Invitado {bounce.target.email} el {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Puede encontrar más detalles en <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Un cordial saludo,<br />
    {cfg:site_name}
</p>