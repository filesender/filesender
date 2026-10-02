<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ziņojuma piegādes kļūme

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Vienam vai vairākiem adresātiem neizdevās saņemt Jūsu ziņojumu(s) :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Pārsūtīt #{bounce.target.transfer.id} adresātam {bounce.target.email} līdz {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Viesim {bounce.target.email} līdz {datetime:bounce.date}
{endif}
{endeach}

Papildinformāciju varat atrast {cfg:site_url}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Vienam vai vairākiem adresātiem neizdevās saņemt Jūsu ziņojumu(s) :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Pārsūtīt #{bounce.target.transfer.id}</a> adresātam {bounce.target.email} līdz {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Viesim {bounce.target.email} līdz {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Papildinformāciju varat atrast <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>