Betreff: Gast beendet das Hochladen von Dateien

{alternative:plain}

Sehr geehrte Damen und Herren,

der folgende Gast beendet das Hochladen von Dateien mit Ihrer Einladung:

Gast: {guest.email}
Ling Einladung: {cfg:site_url}?s=upload&vid={guest.token}

Die Einladung ist bis zum {date:guest.expires} gültig, nach Ablauf des Datums wird er automatisch gelöscht.

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    der folgende Gast beendet das Hochladen von Dateien mit Ihrer Einladung:
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
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>