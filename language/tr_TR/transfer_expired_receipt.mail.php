<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Dosya(lar) zaman aşımına uğradı
subject: (dosyalar zaman aşımına uğradı) {transfer.subject}

{alternative:plain}

Merhaba,

Aktarma numaranız {transfer.id} zaman aşımına uğramıştır ve artık indirilememektedir ({transfer.link}).

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    <a href="{transfer.link}">Transfer n°{transfer.id}</a> has expired and is no longer available for download..
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>