<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: İndirme bilgisi

{alternative:plain}

Merhaba,

{if:files>1}Yüklediğiniz pek çok dosya{else}bir dosya{endif} {if:files>1}{else}{endif} {cfg:site_name} {recipient.email} tarafından indirildi.

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

{files.first().transfer.link} aktarmalar sayfasından dosyalarınıza erişilebilir ve detaylı indirme istatistiklerini görüntüleyebilirsiniz.

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
{if:files>1}Yüklediğiniz pek çok dosya{else}bir dosya{endif} {if:files>1}{else}{endif} {cfg:site_name} {recipient.email} tarafından indirildi.
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
    You can access your files and view detailed download statistics on the transfers page at <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
