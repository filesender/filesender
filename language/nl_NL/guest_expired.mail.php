subject: Gasten voucher verlopen

{alternative:plain}

Geachte heer, mevrouw,

Een gast voucher van {guest.user_email} is verlopen.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    Een gast voucher van <a href="mailto:{guest.user_email}">{guest.user_email}</a> is verlopen.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>
