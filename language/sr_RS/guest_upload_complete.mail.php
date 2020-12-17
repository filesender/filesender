<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gost je završio transfer fajlova

{alternative:plain}

Poštovani,

Sledeći gost je završio transfer fajlova pomoću Vašeg vaučera :

Gost: {guest.email}
Link vaučera: {cfg:site_url}?s=upload&vid={guest.token}

Vaučer je dostupan do {date:guest.expires} i nakon toga će automatski biti obrisan.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Sledeći gost je završio transfer fajlova pomoću Vašeg vaučera :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Informacije o vaučeru</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gost</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Link vaučera</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Važi do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>