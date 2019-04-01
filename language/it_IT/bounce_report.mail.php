<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fallimento di consegna della notifica

{alternative:plain}

Gentile utente,

Uno o più destinatari non ha potuto ricevere i tuoi messaggi:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Trasferimento #{bounce.target.transfer.id} destinatario {bounce.target.email} il {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Opsite {bounce.target.email} il {datetime:bounce.date}
{endif}
{endeach}

Puoi trovare maggiori dettaglio qui {cfg:site_url}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Uno o più destinatari non ha potuto ricevere i tuoi messaggi:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Trasferimento #{bounce.target.transfer.id}</a> destinatario {bounce.target.email} il {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Ospite {bounce.target.email} il {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Puoi trovare maggiori dettagli qui <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>