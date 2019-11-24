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

Szanowni Państwo,

Poniższy gość rozpoczął wysyłanie plików:

Gość: {guest.email}
Link do Kuponu: {cfg:site_url}?s=upload&vid={guest.token}

Kupon jest ważny do {date:guest.expires}, po tym okresie straci ważność.

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Poniższy gość rozpoczął wysyłanie plików:
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