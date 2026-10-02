<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Guest voucher received
subject: {guest.subject}

{alternative:plain}

Dear Sir or Madam,

Please find below a voucher which grants access to {cfg:site_name}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {guest.user_email}
Voucher link: {guest.upload_link}

{if:guest.does_not_expire}
This voucher does not expire.
{else}
The voucher is available until {date:guest.expires} after which time it will be automatically deleted.
{endif}

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
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">This invitation does not expire</td>
{else}
            <td>Valid until</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personal message from {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
