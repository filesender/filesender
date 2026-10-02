<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ваучер гостя надіслано

{alternative:plain}

Шановні панове,

Ваучер, що надає доступ до {cfg:site_name}, було надіслано на адресу {guest.email}.

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Ваучер, що надає доступ до <a href="{cfg:site_url}">{cfg:site_name}</a>, було надіслано на адресу <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
