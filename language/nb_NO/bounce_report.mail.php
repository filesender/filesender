<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Melding ble ikke levert

{alternative:plain}

Hei,

En eller flere av mottakerne klarte ikke å motta meldingen(e) dine:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Overføring #{bounce.target.transfer.id} mottaker {bounce.target.email} den {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gjesteopplaster {bounce.target.email} den {datetime:bounce.date}
{endif}
{endeach}

Flere detaljer er tilgjengelig på {cfg:site_url}

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    En eller flere av mottakerne klarte ikke å motta meldingen(e) dine:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Overføring #{bounce.target.transfer.id}</a> mottaker {bounce.target.email} den {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gjesteopplaster {bounce.target.email} den {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Flere detaljer er tilgjengelig på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med hilsen,<br />
    {cfg:site_name}
</p>
