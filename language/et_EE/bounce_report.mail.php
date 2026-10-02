<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kohaletoimetamine ebaõnnestus

{alternative:plain}

Tere,

Kohaletoimetamine ebaõnnestus ühele või mitmele e-posti aadressile :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

Rohkem infot leiad siit {cfg:site_url}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Kohaletoimetamine ebaõnnestus ühele või mitmele e-posti aadressile : :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> recipient {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Guest {bounce.target.email} on {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Rohkem infot leiad siit <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
