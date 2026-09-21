<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gast hat den Upload von Dateien beendet

{alternative:plain}

Sehr geehrte Damen und Herren,

Der folgende Gast hat den Upload von Dateien durch Verwendung einer Einladung abgeschlossen:

Gast: {guest.email}
Einladungslink: {cfg:site_url}?s=upload&vid={guest.token}

Die Einladung ist bis zum {date:guest.expires} verfügbar. Danach wird sie automatisch gelöscht.

Mit freundlichen Größen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
Der folgende Gast hat den Upload von Dateien durch Verwendung einer Einladung abgeschlossen:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Details der Einladung</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gast</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Einladungslink</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Gültig bis</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Mit freundlichen Grüßen<br />
    {cfg:site_name}
</p>
',