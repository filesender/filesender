<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kupon za gosta je istekao

{alternative:plain}

Poštovani,

Kupon za gosta {guest.user_email} je istekao.

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Kupon za gosta <a href="mailto:{guest.user_email}">{guest.user_email}</a> je istekao.
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>