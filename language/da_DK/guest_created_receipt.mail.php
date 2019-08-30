<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gæstevoucher afsendt

{alternative:plain}

Kære afsender!

En voucher som giver adgang til {cfg:site_name}, er blevet afsendt til {guest.email}.

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
    En voucher som giver adgang til <a href="{cfg:site_url}">{cfg:site_name}</a>, er blevet afsendt til <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>