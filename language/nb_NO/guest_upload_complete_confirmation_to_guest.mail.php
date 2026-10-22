<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gjest fullførte opplastingen

{alternative:plain}

Hei,

Gjesten nedenfor har lastet opp filer med gjesteinvitasjonen:

Gjest: {guest.email}
Invitasjonslenke: {cfg:site_url}?s=upload&vid={guest.token}

Invitasjonen er tilgjengelig til {date:guest.expires}, og blir deretter slettet automatisk.

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
Gjesten nedenfor har lastet opp filer med gjesteinvitasjonen:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Invitasjonsdetaljer</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gjest</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Invitasjonslenke</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Gyldig til</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
',
