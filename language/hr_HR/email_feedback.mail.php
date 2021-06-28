<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Povratne informacije od {if:target_type=="recipient"}primatelja{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}

{alternative:plain}

Poštovani,

Primili smo povratne informacije e-poštom od {if:target_type=="recipient"}primatelja{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}, te se nalaze u prilogu.

Lijep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Primili smo povratne informacije e-poštom od {if:target_type=="recipient"}primatelja{endif}{if:target_type=="guest"}gosta{endif}#{target_id} {target.email}, te se nalaze u prilogu.
</p>

<p>
    Lijep pozdrav,<br />
    {cfg:site_name}
</p>