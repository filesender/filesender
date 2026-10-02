<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Meddelelsen kunne ikke leveres

{alternative:plain}

Kære afsender!

En eller flere af dine modtagere fik ikke meddelelsen:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Overførsel #{bounce.target.transfer.id} modtager {bounce.target.email} på {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gæst {bounce.target.email} på {datetime:bounce.date}
{endif}
{endeach}

Du kan finde flere detaljer på {cfg:site_url}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
    Kære afsender!
</p>

<p>
    En eller flere af dine modtagere fik ikke meddelelsen:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Overførsel #{bounce.target.transfer.id}</a> modtager {bounce.target.email} på {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gæst {bounce.target.email} på {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Du kan finde flere detaljer på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>