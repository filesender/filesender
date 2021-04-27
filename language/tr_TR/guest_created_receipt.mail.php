<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Konuk fişi gönderildi

{alternative:plain}

Merhaba,

{cfg:site_name} erişim sağlayan bir fiş {guest.email} gönderildi.

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Erişim sağlayan bir fiş <a href="{cfg:site_url}">{cfg:site_name}</a> gönderildi  <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>