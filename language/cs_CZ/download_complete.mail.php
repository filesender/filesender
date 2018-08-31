subject: Stahování dokončeno

{alternative:plain}

Vážený uživateli,

Stahování {if:files>1}souborů{else}souboru{endif} bylo dokončeno:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Stahování {if:files>1}souborů{else}souboru{endif} bylo dokončeno:
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().name} ({size:files.first().size})
    {endif}
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
