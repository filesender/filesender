<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Rezumatul zilnic privind transfer

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Găsiți mai jos un rezumat privind descărcările pentru transferul dvs. {transfer.id} (încărcat {date:transfer.created}) :

{if:events} 
{each:events as event}
- Destinatarul {event.who} a descărcat {if:event.what == "archive"}arhiva{else}fișierul {event.what_name}{endif} la {datetime:event.when} 
{endeach}
{else}
Nu sunt descărcări
{endif} 

Pentru detalii suplimentare accesați {transfer.link}

Cu respect,
{cfg:site_name} 

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Găsiți mai jos un rezumat privind descărcările pentru transferul dvs. {transfer.id} (uploaded {date:transfer.created}) : 
</p>

{if:events} 
<ul> 
{each:events as event} 
<li>Destinatarul {event.who} a descărcat {if:event.what == "archive"}arhiva{else} fișierul {event.what_name}{endif} la {datetime:event.when}</li> 
{endeach} 
</ul>
{else} 
<p> 
Nu sunt descărcări 
</p> 
{endif} 

<p> 
Pentru detalii suplimentare accesați <a href="{transfer.link}">{transfer.link}</a> 
</p> 

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>
