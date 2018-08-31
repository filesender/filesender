subject: Notification de téléchargement

{alternative:plain}

Madame, Monsieur,

{if:files>1}Plusieurs fichiers{else}Un fichier{endif} que vous avez déposé {if:files>1}ont{else}a{endif} été téléchargé{if:files>1}s{endif} depuis {cfg:site_name} par {recipient.identity} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Vous trouverez des statistiques détaillées sur {files.first().transfer.link}.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    {if:files>1}Plusieurs fichiers{else}Un fichier{endif} que vous avez déposé {if:files>1}ont{else}a{endif} été téléchargé{if:files>1}s{endif} depuis {cfg:site_name} par {recipient.identity} :
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Vous trouverez des statistiques détaillées sur <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
