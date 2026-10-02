<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Reamintire automată privind expedierea fișierului nr. {transfer.id}

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Un e-mail de reamintire automată a fost trimis destinatarilor care nu au descărcat fișierele dvs. din transferul nr. {transfer.id} pe {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient} 
- {recipient.email} 
{endeach}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Un e-mail de reamintire automată a fost trimis destinatarilor care nu au descărcat fișierele dvs. din transferul <a href="{transfer.link}"> nr. {transfer.id}</a> pe <a href="{cfg:site_url}">{cfg:site_name}</a> : 
</p>

<p> 
<ul> 
{each:recipients as recipient} <li>{recipient.email}</li> 
{endeach} 
</ul> 
</p>

<p>
Cu respect,<br /> 
{cfg:site_name} 
</p>