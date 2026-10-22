<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kvittering for nedlasting

{alternative:plain}

Hei,

{if:files>1}Fleire filer{else}Ei fil{endif} du lasta opp har vorte lasta ned frå {cfg:site_name} av {if:files.first().transfer.get_a_link}ei overføringslenkje:{else}{recipient.email}:{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Du finn filene dine og detaljert nedlastingsstatistikk på overføringssida: {files.first().transfer.link}.

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    {if:files>1}Fleire filer{else}Ei fil{endif} du lasta opp har vorte lasta ned frå {cfg:site_name} av {if:files.first().transfer.get_a_link}ei overføringslenkje.{else}{recipient.email}{endif}
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
    Du finn filene dine og detaljert nedlastingsstatistikk på overføringssida: <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
