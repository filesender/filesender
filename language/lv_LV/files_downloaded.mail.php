<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Lejupielādes apliecinājums

{alternative:plain}

Godātais kungs vai cienījamā kundze,

{if:files>1}Vairāki faili{else}Fails{endif}, ko augšupielādējāt,{if:files>1}ir{else}tika{endif} lejupielādēti no {cfg:site_name} no {if:files.first().transfer.get_a_link} pārsūtīšanas saite:{else}{recipient.email} :{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Jūs varat piekļūt saviem failiem un skatīt detalizētu lejupielādes statistiku pārsūtīšanas lapā šeit {files.first().transfer.link}.

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    {if:files>1}Vairāki faili{else}Fails{endif}, ko augšupielādējāt, {if:files>1}ir{else}tika{endif} lejupielādēti no {cfg:site_name} no {if:files.first().transfer.get_a_link}pārsūtīšanas saite.{else}{recipient.email}{endif}
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
    Jūs varat piekļūt saviem failiem un skatīt detalizētu lejupielādes statistiku pārsūtīšanas lapā šeit <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>