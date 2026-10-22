<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gjesteopplastingstilgang mottatt
subject: {guest.subject}

{alternative:plain}

Hei,

Nedenfor finner du en gjesteopplastingstilgang til {cfg:site_name}. Du kan bruke tilgangen for å laste opp et sett med filer og gjøre den tilgjengelig for en gruppe mennesker.

Utstedt av: {guest.user_email}
Tilgang: {guest.upload_link}

{if:guest.does_not_expire}
Tilgangen utløper ikke.
{else}
Tilgangen kan brukes til {date:guest.expires}. Deretter stenges den automatisk.
{endif}

{if:guest.message}Personlig melding fra {guest.user_email}: {guest.message}{endif}

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
Nedenfor finner du en gjesteopplastingstilgang til <a href="{cfg:site_url}">{cfg:site_name}</a>. Du kan bruke tilgangen for å laste opp et sett med filer og gjøre den tilgjengelig for en gruppe mennesker.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Tilgangsdetaljer</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Utstedt av</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Tilgang </td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">Tilgangen utløper ikke</td>
{else}
            <td>Tilgangen stenges </td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personlig melding fra  {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Med hilsen<br />
    {cfg:site_name}
</p>
