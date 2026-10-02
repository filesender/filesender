<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gast voucher verstuurd

{alternative:plain}

Geachte heer, mevrouw,

Een voucher die toegang verleent tot {cfg:site_name} is verzonden naar {guest.email}.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
    Een voucher die toegang verleent tot <a href="{cfg:site_url}">{cfg:site_name}</a> is verzonden naar <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>

