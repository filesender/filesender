<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (påminnelse) gjesteinvitasjon mottatt
subject: (påminnelse) {guest.subject}

{alternative:plain}

Hei,

Dette er en påminnelse. Nedenfor finner du en invitasjon som gir tilgang til {cfg:site_name}. Du kan bruke den til å laste opp en gruppe filer og gjøre dem tilgjengelige for en gruppe personer.

Avsender: {guest.user_email}
Invitasjonslenke: {guest.upload_link}

Invitasjonen er tilgjengelig til {date:guest.expires}, og blir deretter slettet automatisk.

{if:guest.message}Personlig melding fra {guest.user_email}: {guest.message}{endif}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Dette er en påminnelse. Nedenfor finner du en invitasjon som gir tilgang til <a href="{cfg:site_url}">{cfg:site_name}</a>. Du kan bruke den til å laste opp en gruppe filer og gjøre dem tilgjengelige for en gruppe personer.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Invitasjonsdetaljer</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Avsender</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Invitasjonslenke</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Gyldig til</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personlig melding fra {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
