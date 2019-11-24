<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gość zakończył wysyłanie plików

{alternative:plain}

Szanowni Państwo,

Następujący Gość zakończył przesyłanie plików przy użyciu kuponu:

Gość: {guest.email}
Link Kuponu: {cfg:site_url}?s=upload&vid={guest.token}

Kupon jest dostępny do {date:guest.expires}, po którym to czasie zostanie automatycznie usunięty.

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
Następujący Gość zakończył przesyłanie plików przy użyciu kuponu:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Szczegóły Kuponu</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gość</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Link</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Ważność</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>
',

