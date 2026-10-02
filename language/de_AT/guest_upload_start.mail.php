<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gast hat das Hochladen von Dateien begonnen

{alternative:plain}

Sehr geehrte Damen und Herren,

Der folgende Gast hat das Hochladen von Dateien mit Ihrer Einladung  begonnen:

Gast: {guest.email}
Link Einladung: {cfg:site_url}?s=upload&vid={guest.token}

Die Einladung ist bis zum {date:guest.expires} gültig. Nach Ablauf des Datums wird er automatisch gelöscht.

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    Der folgende Gast hat das Hochladen von Dateien mit Ihrer Einladung  begonnen:
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