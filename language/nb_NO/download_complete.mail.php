<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Nedlasting fullført

{alternative:plain}

Hei,

Din nedlasting av følgende {if:files>1}filer{else}fil{endif} er fullført:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Din nedlasting av følgende {if:files>1}filer{else}fil{endif} er fullført:
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
    Med hilsen,<br />
    {cfg:site_name}
</p>
