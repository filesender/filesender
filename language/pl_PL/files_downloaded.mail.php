<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Potwierdzenie pobrania

{alternative:plain}

Szanowni Państwo,

{if:files>1}Przesłane pliki{else}Przesłany plik{endif} 
{if:files>1}zostały{else}został{endif} pobrany z {cfg:site_name} przez {recipient.email}:

{if:files>1}{each:files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Dostęp do plików i szczegółowych statystyk pobierania można uzyskać na stronie {files.first().transfer.link}.

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    {if:files>1}Przesłane pliki{else}Przesłany plik{endif} {if:files>1}zostały{else}został{endif} pobrany z {cfg:site_name} przez {recipient.email}.
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.name} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Dostęp do plików i szczegółowych statystyk pobierania można uzyskać na stronie <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

