<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Stranica za transfer za gosta

{alternative:plain}

Poštovani,

Gost {guest.email} je pristupio stranici za transfer pomoću Vašeg vaučera.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Gost <a href="mailto:{guest.email}">{guest.email}</a> je pristupio stranici za transfer pomoću Vašeg vaučera.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>