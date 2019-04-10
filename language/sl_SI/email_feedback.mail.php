<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Povratne informacije {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}

{alternative:plain}

Spoštovani,

Prejeli smo sporočilo s povratnimi informacijami s strani {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}, katere najdete v prilogi.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani
</p>

<p>
    Prejeli smo sporočilo s povratnimi informacijami s strani {if:target_type=="recipient"}prejemnika{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}, katere najdete v prilogi.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>