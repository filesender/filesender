<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Alıcınızdan {if:target_type=="recipient"}geri bildirim{endif}{if:target_type=="guest"}guest{endif} {target.email}

{alternative:plain}

Merhaba,

Konuk alıcınızdan bir e-posta aldık {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, bu e-posta ektedir.

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
Konuk alıcınızdan bir e-posta aldık {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, bu e-posta ektedir.
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
