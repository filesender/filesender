subject: Vous avez téléchargé des fichiers vers FileSender en tant qu'invité

{alternative:plain}

Madame, Monsieur,

Vous avez téléchargé des fichiers dans votre bon de visite

invité: {guest.email}
Lien Voucher: {cfg:site_url}?s=upload&vid={guest.token}
Lien de téléchargement: {recipient.download_link}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Vous avez téléchargé des fichiers dans votre bon de visite.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Invité</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Lien Voucher</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Lien de téléchargement</td>
            <td><a href="{transfer.download_link}">{transfer.download_link}</a></td>
        </tr>
        <tr>
            <td>Valable jusque</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
