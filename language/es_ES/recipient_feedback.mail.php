<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tagasiside {if:target_type=="recipient"}saajalt{endif}{if:target_type=="guest"}külaliselt{endif} {target.email}

{alternative:plain}

Tere,

Meile saabus e-postiga tagasiside {if:target_type=="recipient"}saajalt{endif}{if:target_type=="guest"}külaliselt{endif} {target.email}, mis on antud e-kirja lisatud.

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Meile saabus e-postiga tagasiside {if:target_type=="recipient"}saajalt{endif}{if:target_type=="guest"}külaliselt{endif} {target.email}, mis on antud e-kirja lisatud.</p>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
