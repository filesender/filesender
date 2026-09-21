<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: (Reamintire) Fișier{if:transfer.files>1}e{endif} disponibil{if:transfer.files>1}e{endif} pentru descărcare
Subiect: (Reamintire) {transfer.subject}

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Acesta este un e-mail de reamintire: {if:transfer.files>1}fișierele de mai jos au fost încărcate{else}fișierul de mai jos a fost încărcat{endif} pe {cfg:site_name} de către {transfer.user_email} și vi s-a acordat permisiunea de a descărca conținutul {if:transfer.files>1}lor{else}său{endif}:

{if:transfer.files>1}{each:transfer.files as file} 
- {file.path} ({size:file.size}) 
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link de descărcare: {recipient.download_link}

Tranzacția este disponibilă până la {date:transfer.expires}, după care va fi ștearsă automat.

{if:transfer.message || transfer.subject} Mesaj personal de la {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Acesta este un e-mail de reamintire: {if:transfer.files>1}fișierele de mai jos au fost încărcate{else}fișierul de mai jos a fost încărcat{endif} pe <a href="{cfg:site_url}">{cfg:site_name}</a> de către <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> și vi s-a acordat permisiunea de a descărca conținutul {if:transfer.files>1}lor{else}său{endif}. 
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
<ul> {each:transfer.files as file} 
<li>{file.path} ({size:file.size})</li> 
{endeach} 
</ul> 
{else} 
{transfer.files.first().path} ({size:transfer.files.first().size}) 
{endif} 
</td> 
</tr> 
{if:transfer.files>1} <tr> <td>Dimensiune transfer</td> <td>{size:transfer.size}</td>
</tr> {endif}
<tr> 
<td>Dată de expirare</td> 
<td>{date:transfer.expires}</td> 
</tr>
<tr> 
<td>Link de descărcare</td> 
<td><a href="{recipient.download_link}">{recipient.download_link}</a></td> </tr>
</tbody>
</table>

{if:transfer.message}
<p> 
Mesajul personal de la {transfer.user_email}: 
</p>
<p class="message"> 
<span class="subject">{transfer.subject}</span> {transfer.message} 
</p>
{endif}

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>