<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Allalaadimise aruanne

{alternative:plain}

Tere,

{if:files>1}Failid{else}Fail{endif} on allalaetud keskkonnast {cfg:site_name} {recipient.email} poolt:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    {if:files>1}Failid{else}Fail{endif} on allalaetud keskkonnast {cfg:site_name} {recipient.email} poolt.
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
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
