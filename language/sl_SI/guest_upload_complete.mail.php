<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gost je končal z nalaganjem datotek

{alternative:plain}

Spoštovani,

Naslednji gost je končal z nalaganjem datotek preko Vašega vavčerja :

Gost: {guest.email}
Povazava vavčerja: {cfg:site_url}?s=upload&vid={guest.token}

Vavčer je na voljo do {date:guest.expires}. Po pretečenem roku bo avtomatsko izbrisan.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Naslednji gost je končal z nalaganjem datotek preko Vašega vavčerja :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti vavčerja</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Guest</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Povezava vavčerja</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Veljavno do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>