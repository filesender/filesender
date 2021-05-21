<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: (hatırlatıcı) konuk fişi alındı
konu: (hatırlatıcı) {guest.subject}

{alternative:plain}

Merhaba,

Bu bir hatırlatma mesajıdır, {cfg:site_name} erişim hakkı tanıyan bir fiş aşağıdadır. Bu fişi bir dosya grubunu yüklemek ve onu bir insan grubunun indirmesine hazır hale getirmek için kullanabilirsiniz.

Veren: {guest.user_email}
Fiş bağlantısı: {guest.upload_link}

Bu fiş {date:guest.expires} tarihine kadar geçerli olup daha sonra otomatik olarak silinecektir.

{if:guest.message}Kişisel ileti {guest.user_email}: {guest.message}{endif}

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Bu bir hatırlatma mesajıdır, erişim hakkı tanıyan bir fiş aşağıdadır <a href="{cfg:site_url}">{cfg:site_name}</a>.  Bu fişi bir dosya grubunu yüklemek ve onu bir insan grubunun indirmesine hazır hale getirmek için kullanabilirsiniz.
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
    Kişisel ileti {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
