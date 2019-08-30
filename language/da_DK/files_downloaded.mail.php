<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kvittering for hentning

{alternative:plain}

Kære afsender!

{if:files>1}Flere filer{else}En fil{endif} som du har uploadet, er blevet hentet fra {cfg:site_name} af {recipient.email}:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Du kan tilgå dine filer og se detaljeret hentningsstatistik på overførselsoversigten på {files.first().transfer.link}.

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
{if:files>1}Flere filer{else}En fil{endif} som du har uploadet, er blevet hentet fra {cfg:site_name} af {recipient.email}:
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
Du kan tilgå dine filer og se detaljeret hentningsstatistik på overførselsoversigten på <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>