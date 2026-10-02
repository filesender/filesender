<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Račun prenosa

{alternative:plain}

Spoštovani,

{if:files>1}Datoteke, ki ste jih{else}Datoteko, ki ste jo{endif} naložili na stran {cfg:site_name}, je prenesel/a uporabnik/ca {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Do Vaših datotek in njihovih podrobnosti lahko dostopate na strani {files.first().transfer.link}.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    {if:files>1}Datoteke, ki ste jih{else}Datoteko, ki ste jo{endif} naložili na stran {cfg:site_name}, je prenesel/a uporabnik/ca {recipient.email}.
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
    Do Vaših datotek in njihovih podrobnosti lahko dostopate na strani <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>