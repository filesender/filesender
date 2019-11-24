<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Plik(i) nie są już dostępne do pobrania
subject: (Pliki nie są już dostępne do pobrania) {transfer.subject}

{alternative:plain}

Szanowni Państwo,

Transfer nr {transfer.id} został usunięty z {cfg:site_name} przez wysyłającego  ({transfer.user_email}) i nie jest już dostępny do pobrania.

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Transfer nr {transfer.id} został usunięty z <a href="{cfg:site_url}">{cfg:site_name}</a> przez wysyłającego (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) i nie jest już dostępny do pobrania.
</p>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

