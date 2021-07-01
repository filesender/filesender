<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Download completo

{alternative:plain}

Olá,

O download {if:files>1}dos arquivos{else}do arquivo{endif} descrito abaixo terminou :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Olá,
</p>

<p>
   O download {if:files>1}dos arquivos{else}do arquivo{endif} descrito abaixo terminou : 
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
    Atenciosamente,<br />
    {cfg:site_name}
</p>
