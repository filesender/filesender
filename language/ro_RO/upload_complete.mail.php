<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Fișier{if:transfer.files>1}e{endif} au fost încărcat{if:transfer.files>1}e{endif} cu succes

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Vă informăm că {if:transfer.files>1}fișierele de mai jos au fost încărcate{else}fișierul de mai jos a fost încărcat{endif} cu succes pe {cfg:site_name}.

Pentru descărcare folosind următorul link: {transfer.download_link} 

{if:transfer.files>1}{each:transfer.files as file} 
- {file.path} ({size:file.size}) {endeach}{else} {transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Mai multe informații: {transfer.link}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Vă informăm că {if:transfer.files>1}fișierele de mai jos au fost încărcate{else}fișierul de mai jos a fost încărcat{endif} cu succes pe <a href="{cfg:site_url}">{cfg:site_name}</a>. 
</p>

<p> 
Pentru descărcare folosind următorul link <a href="{transfer.download_link}">{transfer.download_link}</a> 
</p>

<table rules="rows">
<thead> 
<tr> 
<th colspan="2">Detalii tranzacție</th> 
</tr> 
</thead>
<tbody>
<tr> 
<td>Fișier{if:transfer.files>1}e{endif}</td> 
<td> 
{if:transfer.files>1} 
<ul> 
{each:transfer.files as file} 
<li>{file.path} ({size:file.size})</li> 
{endeach} 
</ul>
 {else} 
{transfer.files.first().path} ({size:transfer.files.first().size}) 
{endif} 
</td> 
</tr>
<tr> 
<td>Dimensiune</td> 
<td>{size:transfer.size}</td> 
</tr>
<tr> 
<td>Mai multe informații</td> 
<td><a href="{transfer.link}">{transfer.link}</a></td> 
</tr>
</tbody>
</table>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>