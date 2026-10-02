<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Vrijeme za preuzimanje datoteka je isteklo
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Prijenos n°{transfer.id} je istekao i više nije dostupan za preuzimanje ({transfer.link}).

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Prijenos <a href="{transfer.link}"> n°{transfer.id}</a> je istekao i više nije dostupan za preuzimanje.
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>
