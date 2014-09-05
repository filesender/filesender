subject: {cfg:site_name} : Guest voucher cancelled

{alternative:plain}

Dear Sir or Madam,

A guest voucher from {guestvoucher.user_email} has been cancelled.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    A guest voucher from <a href="mailto:{guestvoucher.user_email}">{guestvoucher.user_email}</a> has been cancelled.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
