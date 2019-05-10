<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (reminder) guest voucher received
subject: (reminder) {guest.subject}

{alternative:plain}

Dear Sir or Madam,

This is a reminder, please find below a voucher which grants access to {cfg:site_name}. You can use this voucher to upload one set of files and make it available for download to a group of people.

Issuer: {guest.user_email}
Voucher link: {guest.upload_link}

The voucher is available until {date:guest.expires} after which time it will be automatically deleted.

{if:guest.message}Personal message from {guest.user_email}: {guest.message}{endif}

Best regards,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    This is a reminder, please find below a voucher which grants access to <a href="{cfg:site_url}">{cfg:site_name}</a>. You can use this voucher to upload one set of files and make it available for download to a group of people.
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
            <td>Valid until</td>
            <td>{date:guest.expires}</td>
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

</td></tr>

 <tr style="border-style:none">
    <td align="center">
       <p style="font-size:12px; text-decoration:none">
       More information about the SURFfilesender service can be found at
       <a rel="nofollow" href="https://www.surffilesender.nl/en/" target="_blank">www.surffilesender.nl/en</a>
       </p>
       <p style="font-size:10px; text-decoration:none"> SURFfilesender is powered by <a rel="nofollow" href="https://www.surf.nl/en/" target="_blank">SURF</a>.
       </p>
    </td>
</tr>
</table>
