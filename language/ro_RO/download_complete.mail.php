<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Descărcare completă

{alternative:plain} 

Stimate Domnule / Stimată Doamnă,

Descărcarea {if:files>1}fișierelor{else}fișierului{endif} de mai jos s-a finalizat:

{if:files>1}{each:files as file} 
- {file.path} ({size:file.size}) 
{endeach}{else} 
{files.first().path} ({size:files.first().size}) 
{endif}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Descărcarea {if:files>1}fișierelor{else}fișierului{endif} de mai jos s-a finalizat: 
</p> 

<p> 
{if:files>1} 
<ul> 
{each:files as file} 
<li>{file.path} ({size:file.size})</li> 
{endeach} 
</ul> 
{else} 
{files.first().path} ({size:files.first().size}) 
{endif} 
</p> 

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>
