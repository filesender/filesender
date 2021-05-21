<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Depolama kullanımı uyarısı

{alternative:plain}

Merhaba,

{cfg:site_name} depolama kullanımı uyarı vermektedir

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) yalnızca {size:warning.free_space} alan kaldı ({warning.free_space_pct}%)
{endeach}

Ek detayları {cfg:site_url} adresinde bulabilirsiniz

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    {cfg:site_name} depolama kullanımı uyarı vermektedir
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) yalnızca {size:warning.free_space} alan kaldı ({warning.free_space_pct}%)
{endeach}
</ul>

<p>
    Ek detayları <a href="{cfg:site_url}">{cfg:site_url}</a> adresinde bulabilirsiniz
</p>

<p>
   Saygılarımızla,<br />
    {cfg:site_name}
</p>