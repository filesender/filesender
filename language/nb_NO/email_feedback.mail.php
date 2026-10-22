<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tilbakemelding fra {if:target_type=="recipient"}mottaker{endif}{if:target_type=="guest"}gjesteopplaster{endif}#{target_id} {target.email}

{alternative:plain}

Hei,

Vi har mottatt en tilbakemelding fra {if:target_type=="recipient"}mottaker{endif}{if:target_type=="guest"}gjesteopplaster{endif}#{target_id} {target.email}, som ligger vedlagt denne meldingen.

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Vi har mottatt en tilbakemelding fra {if:target_type=="recipient"}mottaker{endif}{if:target_type=="guest"}gjesteopplaster{endif}#{target_id} {target.email}, som ligger vedlagt denne meldingen.
</p>

<p>
    Med hilsen,<br />
    {cfg:site_name}
</p>