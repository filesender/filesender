<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Napaka pri dostavi sporočila

{alternative:plain}

Spoštovani,

Eden ali več prejemnikov ni uspel prejeti Vašega sporočila :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Prenos #{bounce.target.transfer.id} prejemnik {bounce.target.email} dne {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gost {bounce.target.email} v času {datetime:bounce.date}
{endif}
{endeach}

Podrobnosti lahko najdete na {cfg:site_url}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Eden ali več prejemnikov ni uspel prejeti Vašega sporočila :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Prenos #{bounce.target.transfer.id}</a> prejemnik {bounce.target.email} dne {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gost {bounce.target.email} v času {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Podrobnosti lahko najdete na <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>