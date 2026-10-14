<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
onderwerp: Feedbackformulier
{if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}

{alternative:plain}

Geachte heer, mevrouw,

Wij ontvingen feedback per email van {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}, u vindt deze terug in bijlage.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    Wij ontvingen feedback per email van {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}, u vindt deze terug in bijlage.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>
 