<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (reminder) Teile on loodud vautšer
subject: (reminder) {guest.subject}

{alternative:plain}

Tere,

See on meeldetuletus. Teile on loodud vauter veebisaidis {cfg:site_name}. Saate seda kasutada failide jagamiseks.

Vautšeri looja: {guest.user_email}
Vautšeri link: {guest.upload_link}

Vautšer kehtib kuni {date:guest.expires} peale mida see kustutatakse automaatselt.

{if:guest.message}Personaalne teade aadressilt {guest.user_email}: {guest.message}{endif}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    See on meeldetuletus. Teile on loodud vautšer veebisaidis <a href="{cfg:site_url}">{cfg:site_name}</a>. Saate seda kasutada failide jagamiseks.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Vautšeri üksikasjad</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Vautšeri looja</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Vautšeri link</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Vautšer kehtib kuni</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personaalne teade aadressilt {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
