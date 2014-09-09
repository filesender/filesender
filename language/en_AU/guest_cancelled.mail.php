subject: {cfg:site_name} : Guest voucher cancelled

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
