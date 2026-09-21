<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Confirmare de descărcare

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

{if:files>1}Câteva fișiere încărcate{else}Fișierul încărcat{endif} de dvs. {if:files>1}au fost descărcate{else}a fost descărcat{endif} de pe {cfg:site_name} prin {if:files.first().transfer.get_a_link}linkul de transfer:{else}{recipient.email} :{endif} 

{if:files>1}{each:files as file} 
- {file.path} ({size:file.size}) 
{endeach}{else} 
{files.first().path} ({size:files.first().size}) 
{endif}

Puteți accesa fișierele dvs. și vizualiza statistici detaliate privind descărcările pe pagina de transferuri {files.first().transfer.link}.

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
{if:files>1}Câteva fișiere încărcate{else}Fișierul încărcat{endif} de dvs. {if:files>1}au fost descărcate{else}a fost descărcat{endif} de pe {cfg:site_name} prin {if:files.first().transfer.get_a_link}linkul de transfer.{else}{recipient.email}
{endif} 
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
Puteți accesa fișierele dvs. și vizualiza statistici detaliate privind descărcările pe pagina de transferuri <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>. 
</p>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>