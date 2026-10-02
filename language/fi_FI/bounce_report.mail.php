<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Message delivery failure

{alternative:plain}

Hei!

Yksi tai usempi vastaanottaja ei saanut viestiäsi:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Tiedostojako #{bounce.target.transfer.id} vastaanottaja {bounce.target.email} ajassa {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Käyttäjä {bounce.target.email} {datetime:bounce.date}
{endif}
{endeach}

Lisätietoja osoitteessa {cfg:site_url}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Yksi tai usempi vastaanottaja ei saanut viestiäsi:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Tiedostojako #{bounce.target.transfer.id}</a> vastaanottaja {bounce.target.email} ajassa {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Käyttäjä {bounce.target.email} {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Lisätietoja osoitteessa <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>