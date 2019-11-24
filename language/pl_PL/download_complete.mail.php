<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Pobranie zakończone

{alternative:plain}

Szanowni Państwo,

Pobranie {if:files>1}plików{else}pliku{endif} zostało zakończone:

{if:files>1}{each:files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Z Poważaniem,
{cfg:site_name}

<p>
    Szanowni Państwo,
</p>

<p>
    Pobranie {if:files>1}plików{else}pliku{endif} zostało zakończone:
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
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

