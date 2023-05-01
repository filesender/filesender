<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gast-Einladung erhalten
subject: {guest.subject}

{alternative:plain}

Sehr geehrte Damen und Herren,

unterhalb finden Sie eine Einladung, die den Zugang auf {cfg:site_name} gewährt. Sie können die Einladung verwenden, um einen Satz von Dateien hochzuladen und anschließend einer Gruppe von Personen zum Download zur Verfügung stellen. 

Aussteller/in: {guest.user_email}
Link Einladung: {guest.upload_link}

Die Einladung ist bis zum {date:guest.expires} gültig, nach Ablauf des Datums wird die Einladung automatisch gelöscht.

{if:guest.message} Persönliche Nachricht von {guest.user_email}: {guest.message}{endif}

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
   unterhalb finden Sie eine Einladung, die den Zugang auf <a href="{cfg:site_url}">{cfg:site_name}</a> gewährt. Sie können die Einladung verwenden, um einen Satz von Dateien hochzuladen und anschließend einer Gruppe von Personen zum Download zur Verfügung stellen.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Details zur Einladung</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Aussteller/in</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link Einladung</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Gültig bis</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Persönlich Nachricht von {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Mit freundlichen Grüßen<br />
    {cfg:site_name}
</p>
