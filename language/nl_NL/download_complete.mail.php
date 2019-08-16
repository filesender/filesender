<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Download voltooid

{alternative:plain}

Geachte heer, mevrouw,

Uw download van {if:files>1}onderstaande bestanden{else}onderstaand bestand{endif} is voltooid : 

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
   Gecachte heer, mevrouw,
</p>

<p>
    Uw download van {if:files>1}onderstaande bestanden{else}onderstaand bestand{endif} is voltooid : </p>

<p>
    {if:files>1}
    <ul> {each:files as file}
                <li>{file.path} ({size:file.size})</li>
            {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>