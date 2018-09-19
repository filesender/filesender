<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Un invité a fini de déposer des fichiers

{alternative:plain}

Madame, Monsieur,

Votre invité {guest.email} a fini de déposer des fichiers.

Invité: {guest.email}
Lien de dépôt: {cfg:site_url}?s=upload&vid={guest.token}

Le dépôt est disponible jusqu'au {date:guest.expires}, après coup il sera automatiquement supprimé.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Votre invité {guest.email} a fini de déposer des fichiers.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Détails du dépôt</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Invité</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Lien du dépôt</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Valide jusqu'au</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
