subject: Fichier{if:transfer.files>1}s{endif} disponible{if:transfer.files>1}s{endif} au téléchargement
subject: {transfer.subject}

{alternative:plain}

Madame, Monsieur,

{if:transfer.files>1}Les fichiers suivants ont été déposés{else}Le fichier suivant a été déposé{endif} sur {cfg:site_name} par {transfer.user_email} et {if:transfer.files>1}sont disponibles{else}est disponible{endif} au téléchargement :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Lien de téléchargement: {recipient.download_link}

Le dépôt est valable jusqu'au {date:transfer.expires} après quoi il sera supprimé automatiquement.

{if:transfer.message || transfer.subject}
Message de {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    {if:transfer.files>1}Les fichiers suivants ont été déposés{else}Le fichier suivant a été déposé{endif} sur {cfg:site_name} par {transfer.user_email} et {if:transfer.files>1}sont disponibles{else}est disponible{endif} au téléchargement :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Dépôt</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fichier{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>Taille totale</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Date d'expiration</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Lien de téléchargement</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Message de {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
