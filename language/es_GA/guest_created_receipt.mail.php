<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Guest voucher sent

{alternative:plain}

Dear Sir or Madam,

A voucher granting access to {cfg:site_name} has been sent to {guest.email}.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    A voucher granting access to <a href="{cfg:site_url}">{cfg:site_name}</a> has been sent to <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
