<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ein Gast hat das Hochladen von Dateien beendet

{alternative:plain}

Sehr geehrte Damen und Herren,

der folgende Gast hat das Hochladen von Dateien mit Ihrer Einladung beendet:

Gast: {guest.email}
Ling Einladung: {cfg:site_url}?s=upload&vid={guest.token}

Die Einladung ist bis zum {date:guest.expires} gültig, nach Ablauf des Datums wird er automatisch gelöscht.

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    der folgende Gast hat das Hochladen von Dateien mit Ihrer Einladung beendet:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Details Einladung</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gast</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Link Einladung</td>
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