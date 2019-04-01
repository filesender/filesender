<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Guest access upload page

{alternative:plain}

Товарищ!

Гость с адресом {guest.email} открыл страницу по выданному ранее тобой ваучеру.

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Гость с адресом <a href="mailto:{guest.email}">{guest.email}</a> открыл страницу по выданному ранее тобой ваучеру.
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
