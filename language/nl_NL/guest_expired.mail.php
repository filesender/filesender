<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gastgebruiker voucher verlopen

{alternative:plain}

Geachte heer, mevrouw,

Een gastgebruiker voucher van {guest.user_email} is verlopen.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    Een gastgebruiker voucher van <a href="mailto:{guest.user_email}">{guest.user_email}</a> is verlopen.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>