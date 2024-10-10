<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Guest voucher cancelled

{alternative:plain}

Dear Sir or Madam,

A voucher from {guest.user_email} has been cancelled.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    A voucher from <a href="mailto:{guest.user_email}">{guest.user_email}</a> has been cancelled.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
