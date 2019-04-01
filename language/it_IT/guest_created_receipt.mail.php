<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Voucher inviato

{alternative:plain}

Gentile utente,

Un voucher che concede l'accesso a {cfg:site_name} è stato inviato a {guest.email}.

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Un voucher che concede l'accesso a <a href="{cfg:site_url}">{cfg:site_name}</a> è stato inviato a <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

