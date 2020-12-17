<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Obaveštenje o preuzimanju

{alternative:plain}

Poštovani,

{if:files>1}Fajlovi koje{else}Fajl koji{endif} ste postavili {if:files>1}su preuzeti{else}je preuzet{endif} sa {cfg:site_name} od {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Svojim fajlovima možete pristupiti i pregledati detaljnu statistiku preuzimanja na stranici transfera na {files.first().transfer.link}.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    {if:files>1}Fajlovi koje{else}Fajl koji{endif} ste postavili {if:files>1}su preuzeti{else}je preuzet{endif} sa {cfg:site_name} od {recipient.email}.
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
    Svojim fajlovima možete pristupiti i pregledati detaljnu statistiku preuzimanja na stranici transfera na <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>