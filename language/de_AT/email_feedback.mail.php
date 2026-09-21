<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback von {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} Nr. {target_id} {target.email}

{alternative:plain}

Sehr geehrte Damen und Herren,

wir erhielten eine Feedback E-Mail von {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} Nr. {target_id} {target.email}, finden Sie beigefügt.

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    wir erhielten eine Feedback E-Mail von {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} Nr. {target_id} {target.email}, finden Sie beigefügt.
</p>

<p>
    Mit freundliche Grüßen<br />
    {cfg:site_name}
</p>
