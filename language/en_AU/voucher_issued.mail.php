subject: {cfg:site_name}: Voucher received

{alternative:plain}

Dear Sir or Madam,

Please find below a voucher which grants access to {cfg:site_name}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {guestvoucher.user_email}
Voucher link: {cfg:site_url}?vid={guestvoucher.token}

The voucher is available until {date:guestvoucher.expires} after which time it will be automatically deleted.

{if:guestvoucher.message}Personal message from {guestvoucher.user_email}: {guestvoucher.message}{endif}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Please find below a voucher which grants access to <a href="{cfg:site_url}">{cfg:site_name}</a>. You can use this voucher to upload one set of files and make it available for download to a group of people.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Issuer</td>
            <td><a href="mailto:{guestvoucher.user_email}">{guestvoucher.user_email}</a></td>
        </tr>
        <tr>
            <td>Voucher link</td>
            <td><a href="{cfg:site_url}?vid={guestvoucher.token}">{cfg:site_url}?vid={guestvoucher.token}</a></td>
        </tr>
        <tr>
            <td>Valid until</td>
            <td>{date:guestvoucher.expires}</td>
        </tr>
    </tbody>
</table>

{if:guestvoucher.message}
<p>
    Personal message from {guestvoucher.user_email}: {guestvoucher.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
