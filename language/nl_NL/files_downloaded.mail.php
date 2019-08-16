<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ontvangstbevestiging

{alternative:plain}

Geachte heer, mevrouw,

{if:files>1}Een aantal bestanden{else}Een bestand{endif} dat u heeft geüpload{if:files>1} is{else}is{endif} gedownload van {cfg:site_name} door {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

U kunt toegang krijgen tot uw bestanden en meer details over downloadstatistieken voor deze transfers bekijken op de transfers-pagina op {files.first().transfer.link}.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    {if:files>1}Een aantal bestanden{else}Een bestand{endif} dat u heeft geüpload{if:files>1}werd{else}werd{endif} gedownload van {cfg:site_name} door {recipient.email}
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
    U kunt toegang krijgen tot uw bestanden en meer details over downloadstatistieken voor deze transfers bekijken op de transfers-pagina op <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>