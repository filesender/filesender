<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gjesteinvitasjon motteken
subject: {guest.subject}

{alternative:plain}

Hei,

Nedanfor finn du ein invitasjon som gir tilgang til {cfg:site_name}. Du kan bruke han til å laste opp ei gruppe filer og gjere dei tilgjengelege for ei gruppe personar.

Avsendar: {guest.user_email}
Invitasjonslenkje: {guest.upload_link}

{if:guest.does_not_expire}
Denne invitasjonen går ikkje ut på dato.
{else}
Invitasjonen er tilgjengeleg til {date:guest.expires}, og blir deretter sletta automatisk.
{endif}

{if:guest.message}Personleg melding frå {guest.user_email}: {guest.message}{endif}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Nedanfor finn du ein invitasjon som gir tilgang til <a href="{cfg:site_url}">{cfg:site_name}</a>. Du kan bruke han til å laste opp ei gruppe filer og gjere dei tilgjengelege for ei gruppe personar.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Invitasjonsdetaljar</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Avsendar</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Invitasjonslenkje</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">Denne invitasjonen går ikkje ut på dato</td>
{else}
            <td>Gyldig til</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personleg melding frå {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
