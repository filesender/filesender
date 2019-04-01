<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ricevuta di download

{alternative:plain}

Gentile utente,

{if:files>1}Alcuni file{else}Il file{endif} caricato {if:files>1}sono stati{else}è stato{endif} scaricato con {cfg:site_name} da {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Puoi accedere ai tuoi file e visualizzare le statistiche dettagliate sul download nella pagina dei trasferimenti su {files.first().transfer.link}.

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    {if:files>1}Alcuni file{else}Il file{endif} caricato {if:files>1}sono stati{else}è stato{endif} scaricato con {cfg:site_name} da {recipient.email}.
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
    Puoi accedere ai tuoi file e visualizzare le statistiche dettagliate sul download nella pagina dei trasferimenti su <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

