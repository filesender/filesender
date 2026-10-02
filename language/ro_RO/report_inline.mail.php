<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Raport despre {target.type} #{target.id}

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Găsiți mai jos raportul despre {target.type}:

{target.type} nr. {target.id}

{if:target.type == "Transfer"}
Acest transfer conține {transfer.files} fișiere cu o dimensiune totală de {size:transfer.size}.

Acest transfer este/a fost disponibil până la {date:transfer.expires}.

Acest transfer a fost expediat către {transfer.recipients} destinatari. 
{endif}
{if:target.type == "File"}
Acest fișier se numește {file.path}, are o dimensiune de {size:file.size} și este/a fost disponibil până la {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Acest destinatar are adresa de e-mail {recipient.email} și este (a fost) valabil până la {date:recipient.expires}.
{endif}

Găsiți mai jos jurnalul complet privind ce s-a întâmplat cu transferul:

{raw:content.plain}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Găsiți mai jos raportul despre {target.type}:<br /><br />

{target.type} nr. {target.id}<br /><br />

{if:target.type == "Transfer"}
Acest transfer conține {transfer.files} fișiere cu o dimensiune totală de {size:transfer.size}.<br /><br />

Acest transfer este/a fost disponibil până la {date:transfer.expires}.<br /><br />

Acest transfer a fost expediat către {transfer.recipients} destinatari.
{endif} 
{if:target.type == "File"}
Acest fișier se numește {file.path}, are o dimensiune de {size:file.size} și este/a fost disponibil până la {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Acest destinatar are adresa de e-mail {recipient.email} și este (a fost) valabil până la {date:recipient.expires}. 
{endif} 
</p>

<p> 
Găsiți mai jos jurnalul complet privind ce s-a întâmplat cu transferul:
<table class="auditlog" rules="rows"> 
<thead>
<th>Data</th>
<th>Eveniment</th> 
<th>Adresă IP</th> 
</thead> 
<tbody> 
{raw:content.html} 
</tbody>
</table> 
</p>

<p>Cu respect,<br/> 
{cfg:site_name}</p>