<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Nedlastings kvittering

{alternative:plain}

Hei,

{if:files>1}Flere filer{else}En fil{endif} du lastet opp har blitt lastet ned fra {cfg:site_name} {if:files.first().transfer.get_a_link}fra en overføringslinke:{else}av {recipient.email} :{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Du kan se filen(e) og detaljert nedlastingsstatistikk på overføringsiden på {files.first().transfer.link}.

Med hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    {if:files>1}Flere filer{else}En fil{endif} du lastet opp har blitt lastet ned fra {cfg:site_name} {if:files.first().transfer.get_a_link}fra en overførings linke{else}av {recipient.email}{endif}.
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
    Du kan se filen(e) og detaljert nedlastingsstatistikk på overføringsiden på  <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Med hilsen,<br />
    {cfg:site_name}
</p>
