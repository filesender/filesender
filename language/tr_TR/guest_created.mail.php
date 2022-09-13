<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Konuk fişi alındı                                                                                  
konu: {guest.subject}

{alternative:plain}

Merhaba,

{cfg:site_name} erişim hakkı tanıyan bir fiş aşağıdadır. Bu fişi bir dosya grubunu yüklemek ve bir kişi grubunun indirmesine hazır hale getirmek için kullanabilirsiniz.

Veren: {guest.user_email}
Fiş bağlantısı: {guest.upload_link}

Bu fiş {date:guest.expires} tarihine kadar geçerli olup daha sonra otomatik olarak silinecektir.

{if:guest.message}Personal message from {guest.user_email}: {guest.message}{endif}

Saygılarımıza,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
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
            <td>Valid until</td>
{if:guest.does_not_expire}
            <td>asla</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
        {guest.user_email} adresinden kişisel ileti:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
