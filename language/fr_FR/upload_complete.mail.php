subject: Fichier{if:transfer.files>1}s{endif} téléversé avec succès

{alternative:plain}

Madame, Monsieur,

{if:transfer.files>1}Les fichiers suivants ont été téléversés{else}Le fichier suivant a été téléversé{endif} avec succès sur {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Plus d'informations : {transfer.link}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    {if:transfer.files>1}Les fichiers suivants ont été téléversés{else}Le fichier suivant a été téléversé{endif} avec succès sur <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Détails</th>
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
        <tr>
            <td>Taille totale</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Plus d'informations</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Vous pourrez trouver plus de détails sur <a href="{cfg:site_url}?s=transfers">{cfg:site_url}?s=transfers</a>
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
