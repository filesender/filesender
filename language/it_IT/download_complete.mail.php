<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Download completato

{alternative:plain}

Gentile utente,

Il download {if:files>1}dei{else}del{endif} file è terminato :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Il download {if:files>1}dei{else}del{endif} file è terminato :
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
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

