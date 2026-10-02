<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Invitatul a finalizat încărcarea fișierelor

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Invitatul de mai jos a finalizat încărcarea fișierelor, utilizând voucher:

Invitat: {guest.email}
Link voucher: {cfg:site_url}?s=upload&vid={guest.token}

Voucherul este valabil până la {date:guest.expires}, după ce va fi șters automat.

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Următorul invitat a finalizat încărcarea fișierelor folosind un voucher pentru invitat: 
</p>

<table rules="rows"> 
<thead> 
<tr> 
<th colspan="2">Detalii voucher</th> 
</tr> 
</thead> 
<tbody> 
<tr> 
<td>Invitat</td> 
<td><a href="mailto:{guest.email}">{guest.email}</a></td> 
</tr> 
<tr> 
<td>Voucher link</td> 
<td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td> 
</tr> 
<tr> 
<td>Valabil până la</td> 
<td>{date:guest.expires}</td> 
</tr> 
</tbody> 
</table>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>
',