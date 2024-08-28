<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Potvrzení o stažení

{alternative:plain}

Vážený uživateli,

{if:files>1}Několik souborů nahraných{else}Soubor nahraný{endif} Vámi {if:files>1}bylo staženo{else}byl stažen{endif} z {cfg:site_name} {if:files.first().transfer.get_a_link}přenosovým odkazem:{else}příjemcem {recipient.email} :{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

Ke svým souborům a detailním statistikám se dostanete na {files.first().transfer.link}.

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    {if:files>1}Několik souborů nahraných{else}Soubor nahraný{endif} Vámi {if:files>1}bylo staženo{else}byl stažen{endif} z {cfg:site_name} {if:files.first().transfer.get_a_link}přenosovým odkazem.{else}příjemcem {recipient.email}{endif}
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().name} ({size:files.first().size})
    {endif}
</p>

<p>
    Ke svým souborům a detailním statistikám se dostanete na <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
