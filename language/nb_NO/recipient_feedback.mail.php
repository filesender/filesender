<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tilbakemelding fra {if:target_type=="recipient"}mottakeren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}

{alternative:plain}

Hei,

Vi har fått en tilbakemelding på e-post fra {if:target_type=="recipient"}mottakeren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}. Se vedlegget.

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Vi har fått en tilbakemelding på e-post fra {if:target_type=="recipient"}mottakeren{endif}{if:target_type=="guest"}gjesten{endif} {target.email}. Se vedlegget.
</p>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
