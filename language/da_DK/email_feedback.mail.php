<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback fra {if:target_type=="recipient"}modtageren{endif}{if:target_type=="guest"}gæsten{endif}#{target_id} {target.email}

{alternative:plain}
Kære afsender!

Vi har fået feedback på e-mail fra {if:target_type=="recipient"}modtageren{endif}{if:target_type=="guest"}gæsten{endif}#{target_id} {target.email}. Se venligst vedhæftede.

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>

Vi har fået feedback på e-mail fra {if:target_type=="recipient"}modtageren{endif}{if:target_type=="guest"}gæsten{endif}#{target_id} {target.email}. Se venligst vedhæftede.
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>