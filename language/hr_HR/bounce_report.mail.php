<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Neuspjeh u isporuci poruke

{alternative:plain}

Poštovani,

Jedan ili više vaših primatelja nije uspjelo primiti vašu poruku :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Prijenos #{bounce.target.transfer.id} primatelj {bounce.target.email} dana {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gost {bounce.target.email} dana {datetime:bounce.date}
{endif}
{endeach}

Dodatne pojedinosti možete naći na {cfg:site_url}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Jedan ili više vaših primatelja nije uspjelo primiti vašu poruku :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Prijenos #{bounce.target.transfer.id}</a> primatelj {bounce.target.email} dana {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gost {bounce.target.email} dana {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Dodatne pojedinosti možete naći na <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>