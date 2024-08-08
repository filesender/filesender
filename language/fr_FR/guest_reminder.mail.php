<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (rappel) Invitation reçue
subject: (rappel) {guest.subject}

{alternative:plain}

Madame, Monsieur,

Madame, Monsieur,

Ceci est un rappel. Veuillez trouver ci-dessous une invitation donnant accès à <a href="{cfg:site_url}">{cfg:site_name}</a>. Vous pouvez utiliser cette invitation pour déposer un ensemble de fichiers et le rendre disponible au téléchargement pour un group de personnes.

Emetteur: {guest.user_email}
Lien de dépôt: {guest.upload_link}

Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.

{if:guest.message}Message de {guest.user_email}: {guest.message}{endif}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Ceci est un rappel. Veuillez trouver ci-dessous une invitation donnant accès à <a href="{cfg:site_url}">{cfg:site_name}</a>. Vous pouvez utiliser cette invitation pour déposer un ensemble de fichiers et le rendre disponible au téléchargement pour un group de personnes.
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
            <td>Valide jusqu'au</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Message de {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
