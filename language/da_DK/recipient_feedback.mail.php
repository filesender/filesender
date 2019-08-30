<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback fra din {if:target_type=="recipient"}modtager{endif}{if:target_type=="guest"}gæst{endif} {target.email}

{alternative:plain}

Kære afsender!

Vi har fået feedback på e-mail fra {if:target_type=="recipient"}modtageren{endif}{if:target_type=="guest"}gæsten{endif} {target.email}. Se venligst vedhæftningen.

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
Vi har fået feedback på e-mail fra {if:target_type=="recipient"}modtageren{endif}{if:target_type=="guest"}gæsten{endif} {target.email}. Se venligst vedhæftningen.
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>