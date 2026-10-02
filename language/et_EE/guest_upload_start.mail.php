<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Külaline alustas failide üleslaadimist

{alternative:plain}

Tere,

Külaline alsutas failide üleslaadimist:

Külaline: {guest.email}
Vautšeri link: {cfg:site_url}?s=upload&vid={guest.token}

Vautšer kehtib kuni {date:guest.expires} peale mida see kustutatakse automaatselt.

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Külaline alustas failide üleslaadimist:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Vautšeri üksikasjad</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Külaline</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Vautšeri link</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Vautšer kehtib kuni</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
