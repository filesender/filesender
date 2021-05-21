<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Dosya(lar) silindi
konu: (files deleted) {transfer.subject}

{alternative:plain}

Merhaba,

Aktarma numaranız {transfer.id} {cfg:site_name} silinmiştir ve artık indirilememektedir ({transfer.link}).

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Hello,
</p>

<p>
    <a href="{transfer.link}">Transfer n°{transfer.id}</a> has been deleted from <a href="{cfg:site_url}">{cfg:site_name}</a> and is no longer available for download.
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
