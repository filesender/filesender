subject: Guest access upload page

{alternative:plain}

Dear Sir or Madam,

The guest {guest.email} has accessed to the upload page from your voucher.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    The guest <a href="mailto:{guest.email}">{guest.email}</a> has accessed to the upload page from your voucher.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
