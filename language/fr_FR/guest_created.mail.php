<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Invitation
subject: {guest.subject}

{alternative:plain}

Madame, Monsieur,

Veuillez trouver ci-dessous une invitation donnant accès à {cfg:site_name}. Vous pouvez utiliser cette invitation pour déposer un ensemble de fichiers et le rendre disponible au téléchargement à un groupe de personnes.

Expéditeur: {guest.user_email}
Lien de dépôt: {guest.upload_link}

{if:guest.does_not_expire}
Cette invitation n'expirera pas.
{else}
Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.
{endif}

{if:guest.message}Message de {guest.user_email}: {guest.message}{endif}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Veuillez trouver ci-dessous une invitation donnant accès à <a href="{cfg:site_url}">{cfg:site_name}</a>. Vous pouvez utiliser cette invitation pour déposer un ensemble de fichiers et le rendre disponible au téléchargement pour un group de personnes.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Détails de l'invitation</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Emetteur</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Lien de dépôt</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">Cette invitation n'expirera pas</td>
{else}
            <td>Valide jusqu'au</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Message personnel de {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
