<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Konuk dosyaları yüklemeye başladı

{alternative:plain}

Merhaba,

Aşağıdaki konuk sizin fişinizden dosyaları yüklemeye başlamıştır:

Konuk: {guest.email}
Fiş bağlantısı: {cfg:site_url}?s=upload&vid={guest.token}

Bu fiş {date:guest.expires} tarihine kadar geçerli olup sonrasında otomatik olarak silinecektir.

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Aşağıdaki konuk sizin fişinizden dosyaları yüklemeye başlamıştır :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Konuk</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Fiş bağlantısı</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Tarihine kadar geçerli</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>