<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Descarga completada

{alternative:plain}

Estimado señor o señora,

Su descarga de {if:files>1}los archivos{else}el archivo{endif} bellow has ended :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Your download of the {if:files>1}files{else}file{endif} bellow has ended :
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
    Best regards,<br />
    {cfg:site_name}
</p>