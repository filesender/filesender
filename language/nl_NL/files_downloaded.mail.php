onderwerp: Ontvangstbevestiging

{alternative:plain}

Geachte mevrouw, heer,

{if:files>1}Een aantal bestanden{else}Een bestand{endif} dat u heeft geupload{if:files>1}werd{else}werd{endif} gedownload van {cfg:site_name} door {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

U kunt uw bestanden benaderen en meer details over downloadstatistieken voor deze transfers bekijken op de transfers-pagina op {files.first().transfer.link}.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    {if:files>1}Een aantal bestanden{else}Een bestand{endif} dat u heeft geupload{if:files>1}werd{else}werd{endif} gedownload van {cfg:site_name} door {recipient.email}
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
    U kunt uw bestanden benaderen en meer details over downloadstatistieken voor deze transfers bekijken op de transfers-pagina op <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>