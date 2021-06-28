<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Preuzimanje završeno

{alternative:plain}

Poštovani,

Vaše preuzimanje {if:files>1}datoteka{else}datoteke{endif} niže navedeno je završeno :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Lijep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Vaše preuzimanje {if:files>1}datoteka{else}datoteke{endif} niže navedeno je završeno :
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
    Lijep pozdrav,<br />
    {cfg:site_name}
</p>