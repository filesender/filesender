<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gæstevoucher annulleret

{alternative:plain}

Kære gæst!

En voucher fra {guest.user_email} er blevet annulleret.

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
    En voucher fra <a href="mailto:{guest.user_email}">{guest.user_email}</a> er blevet annulleret.
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>