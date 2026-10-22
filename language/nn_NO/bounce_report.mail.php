<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Melding vart ikkje levert

{alternative:plain}

Hei,

Ein eller fleire av mottakarane klarte ikkje å ta imot meldinga(ne) dine:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Overføring #{bounce.target.transfer.id} mottakar {bounce.target.email} den {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gjestopplastar {bounce.target.email} den {datetime:bounce.date}
{endif}
{endeach}

Fleire detaljar er tilgjengelege på {cfg:site_url}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Ein eller fleire av mottakarane klarte ikkje å ta imot meldinga(ne) dine:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Overføring #{bounce.target.transfer.id}</a> mottakar {bounce.target.email} den {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gjest {bounce.target.email} den {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Fleire detaljar er tilgjengelege på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
