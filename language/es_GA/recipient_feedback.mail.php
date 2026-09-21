<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}

{alternative:plain}

Dear Sir or Madam,

We received an email feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, please find it enclosed.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    We received an email feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, please find it enclosed.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
