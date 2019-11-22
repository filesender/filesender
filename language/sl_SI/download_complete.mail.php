<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Prenos končan

{alternative:plain}

Spoštovani,

Vaš prenos {if:files>1}datotek, navedenih{else}datoteke, navedene{endif} spodaj, se je končal :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Vaš prenos {if:files>1}datotek, navedenih{else}datoteke, navedene{endif} spodaj, se je končal :
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
    Lep pozdrav,<br />
    {cfg:site_name}
</p>