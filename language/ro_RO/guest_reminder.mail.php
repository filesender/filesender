<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: (Reamintire) Ați primit voucherul pentru invitat
subject: (reminder) {guest.subject}

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Acesta este un e-mail de reamintire. Mai jos găsiți un voucher care oferă acces la {cfg:site_name}. Puteți utiliza acest voucher pentru a încărca fișiere și a le pune la dispoziția unui grup de persoane pentru descărcare.

Emitent: {guest.user_email}
Link voucher: {guest.upload_link}

Voucherul este valabil până la {date:guest.expires}, după ce va fi șters automat. 

{if:guest.message}Mesajul personal de la {guest.user_email}: {guest.message}{endif}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Acesta este un e-mail de reamintire. Mai jos găsiți un voucher care oferă acces la <a href="{cfg:site_url}">{cfg:site_name}</a>. Puteți utiliza acest voucher pentru a încărca fișiere și a le pune la dispoziția unui grup de persoane pentru descărcare. 
</p>

<table rules="rows"> 
<thead> 
<tr> 
<th colspan="2">Detalii voucher</th> 
</tr> 
</thead> 
<tbody> 
<tr> 
<td>Emitent</td> 
<td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td> 
</tr> 
<tr> 
<td>Link voucher</td> 
<td><a href="{guest.upload_link}">{guest.upload_link}</a></td> 
</tr> 
<tr> 
<td>Valabil până la</td> 
<td>{date:guest.expires}</td> 
</tr> 
</tbody> 
</table>

{if:guest.message}
<p> 
Mesajul personal de la {guest.user_email}: 
</p> 
<p class="message"> {guest.message} 
</p> 
{endif}

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>