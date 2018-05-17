subject: Potvrzení o stažení

{alternative:plain}

Vážený uživateli,

{if:files>1}Několik souborů nahraných{else}Soubor nahraný{endif} Vámi {if:files>1}bylo staženo{else}byl stažen{endif} z {cfg:site_name} příjemcem {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

Ke svým souborům a detailním statistikám se dostanete zde: {files.first().transfer.link}.

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    {if:files>1}Několik souborů nahraných{else}Soubor nahraný{endif} Vámi {if:files>1}bylo staženo{else}byl stažen{endif} z {cfg:site_name} příjemcem {recipient.email} :
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
    Ke svým souborům a detailním statistikám se dostanete zde: <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

