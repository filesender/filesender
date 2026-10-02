<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Povratne informacije Vašega {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif} {target.email}

{alternative:plain}

Spoštovani,

prejeli smo e-pošto s povratnimi informacijami Vašega {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif} {target.email}, katero prilagamo v priponki.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    prejeli smo e-pošto s povratnimi informacijami Vašega {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif} {target.email}, katero prilagamo v priponki.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>