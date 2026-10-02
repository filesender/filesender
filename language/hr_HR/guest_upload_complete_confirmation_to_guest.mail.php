<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gost je završio prijenos datoteka

{alternative:plain}

Poštovani,

Slijedeći gost je završio prijenos datoteka pomoću kupona :

Gost: {guest.email}
Poveznica na kupon: {cfg:site_url}?s=upload&vid={guest.token}

Kupon je dostupan do {date:guest.expires} i nakon toga će automatski biti obrisan.

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
Slijedeći gost je završio prijenos datoteka pomoću kupona :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Informacije o kuponu</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gost</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Poveznica na kupon</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Vrijedi do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>