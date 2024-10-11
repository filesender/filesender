<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Guest start to upload files

{alternative:plain}

Dear Sir or Madam,

The following guest started to upload files from your voucher :

Guest: {guest.email}
Voucher link: {cfg:site_url}?s=upload&vid={guest.token}

The voucher is available until {date:guest.expires} after which time it will be automatically deleted.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    The following guest started to upload files from your voucher :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Guest</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Voucher link</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Valid until</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
