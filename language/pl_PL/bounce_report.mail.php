<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Nieudane dostarczanie wiadomości

{alternative:plain}

Szanowni Państwo, 

Co najmniej jeden z adresatów nie otrzymał wiadomości:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} w dniu {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gość {bounce.target.email} w dniu {datetime:bounce.date}
{endif}
{endeach}

Możesz znaleźć dodatkowe informacje na {cfg: site_url}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>     
Szanowni Państwo, 
</p> 

<p>
     Co najmniej jeden z Twoich adresatów nie otrzymał wiadomości: 
</p> 

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> odbiorca {bounce.target.email} w dniu {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gość{bounce.target.email} w dniu {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
     Możesz znaleźć dodatkowe szczegóły na <a href="{cfg:site_url}"> {cfg: site_url} </a> </p> 
<p>
     Z Poważaniem, <br />
     {cfg: site_name} 
</p>

