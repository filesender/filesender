<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Eșec la livrarea mesajului

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Unul sau mai mulți dintre destinatarii dvs. nu au reușit să primească mesajul:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
- Transfer #{bounce.target.transfer.id} destinatar {bounce.target.email} 
la {datetime:bounce.date} ({bounce.target.transfer.link}) 
{endif}{if:bounce.target_type=="Guest"}
- Invitat {bounce.target.email} la {datetime:bounce.date}
{endif}
{endeach}

Pentru detalii suplimentare accesați {cfg:site_url}

Cu respect,
{cfg:site_name}

{alternative:html}

<p>
Stimate Domnule / Stimată Doamnă, 
</p>

<p>
Unul sau mai mulți dintre destinatarii dvs. nu au reușit să primească mesajul: 
</p> 

<ul> 
{each:bounces as bounce} 
<li> 
{if:bounce.target_type=="Recipient"}
<a href="{bounce.target.transfer.link}">Transfer #
{bounce.target.transfer.id}</a> destinatar {bounce.target.email} la {datetime:bounce.date} 
{endif}{if:bounce.target_type=="Guest"}
Invitat {bounce.target.email} la {datetime:bounce.date} 
{endif} 
</li> 
{endeach}
</ul> 

<p> 
Pentru detalii suplimentare accesați <a href="{cfg:site_url}">{cfg:site_url}</a> 
</p>

<p> Cu respect,<br /> 
{cfg:site_name} 
</p>
