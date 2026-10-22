<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tilbakemelding frå {if:target_type=="recipient"}mottakaren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}

{alternative:plain}

Hei,

Vi har fått ei tilbakemelding på e-post frå {if:target_type=="recipient"}mottakaren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}. Sjå vedlegget.

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Vi har fått ei tilbakemelding på e-post frå {if:target_type=="recipient"}mottakaren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}. Sjå vedlegget.
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
