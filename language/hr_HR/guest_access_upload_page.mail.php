<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Stranica za prijenos za gosta

{alternative:plain}

Poštovani,

Gost {guest.email} je pristupio stranici za prijenos pomoću Vašeg kupona.

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Gost <a href="mailto:{guest.email}">{guest.email}</a> je pristupio stranici za prijenos pomoću Vašeg kupona.
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>