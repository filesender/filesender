<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Overførsel færdig

{alternative:plain}

Kære modtager!

Din hentning af {if:files>1}filerne{else}filen{endif} herunder er færdig:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære modtager!
</p>

<p>
Din hentning af {if:files>1}filerne{else}filen{endif} herunder er færdig:
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
    Med venlig hilsen<br />
    {cfg:site_name}
</p>