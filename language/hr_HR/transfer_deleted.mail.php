<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Datoteke više nisu dostupne za preuzimanje
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Prijenos n°{transfer.id} je obrisan sa {cfg:site_name} od pošiljatelja ({transfer.user_email}) i nije više dostupan za preuzimanje.

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Prijenos n°{transfer.id} je obrisan sa <a href="{cfg:site_url}">{cfg:site_name}</a> od pošiljatelja (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) i nije više dostupan za preuzimanje.
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>
