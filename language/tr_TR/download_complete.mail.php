<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: İndirme tamamlandı

{alternative:plain}

Merhaba,

Aşağıdaki {if:files>1}files{else}file{endif} dosyalarının indirilmesi tamamlanmıştır:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
Aşağıdaki {if:files>1}files{else}file{endif} dosyalarının indirilmesi tamamlanmıştır:
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
   Saygılarımızla,<br />
    {cfg:site_name}
</p>
