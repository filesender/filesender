<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Slanje kupona za gosta

{alternative:plain}

Poštovani,

Kupon koji omogućava pristup na {cfg:site_name} je poslan {guest.email}.

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    AKupon koji omogućava pristup na <a href="{cfg:site_url}">{cfg:site_name}</a> je poslan <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>