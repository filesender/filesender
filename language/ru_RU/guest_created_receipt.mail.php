<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Гостевой ваучер отправлен

{alternative:plain}

Товарищ!

Ваучер для доступа к {cfg:site_name} был отправлен {guest.email}.

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Ваучер для доступа к <a href="{cfg:site_url}">{cfg:site_name}</a> был отправлен <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
