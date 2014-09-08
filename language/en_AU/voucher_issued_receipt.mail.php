subject: {cfg:site_name}: Voucher sent

{alternative:plain}

Dear Sir or Madam,

A guest voucher granting access to {cfg:site_name} has been sent to {guestvoucher.email}.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    A guest voucher granting access to <a href="{cfg:site_url}">{cfg:site_name}</a> has been sent to <a href="mailto:{guestvoucher.email}">{guestvoucher.email}</a>.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
