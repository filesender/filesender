<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Dosya(lar) artık indirilemez

{alternative:plain}

Merhaba,

Aktarma numarası {transfer.id} gönderici ({transfer.user_email}) tarafından {cfg:site_name} silinmiştir ve artık indirilememektedir.

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Aktarma numarası {transfer.id}  <a href="{cfg:site_url}">{cfg:site_name}</a> gönderici (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) tarafından silinmiştir ve artık indirilememektedir.
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
