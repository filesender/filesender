subject: {cfg:site_name}: Guest voucher received

{alternative:plain}

Dear Sir or Madam,

Please find below a voucher which grants access to {cfg:site_name}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {guest.user_email}
Voucher link: {cfg:site_url}?vid={guest.token}

The voucher is available until {date:guest.expires} after which time it will be automatically deleted.

{if:guest.message}Personal message from {guest.user_email}: {guest.message}{endif}

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
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Voucher link</td>
            <td><a href="{cfg:site_url}?vid={guest.token}">{cfg:site_url}?vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Valid until</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personal message from {guest.user_email}: {guest.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
