<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Slanje vaučera za gosta

{alternative:plain}

Poštovani,

Vaučer koji omogućava pristup na {cfg:site_name} je poslat {guest.email}.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Vaučer koji omogućava pristup na <a href="{cfg:site_url}">{cfg:site_name}</a> je poslat <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>