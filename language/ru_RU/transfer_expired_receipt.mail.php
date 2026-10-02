<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: File(s) expired
subject: (files expired) {transfer.subject}

{alternative:plain}

Товарищ!

Твой трансфер под номером {transfer.id} истек по сроку давности и больше недоступен для скачивания ({transfer.link}).

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Твой <a href="{transfer.link}">трансфер под номером{transfer.id}</a> истек по сроку давности и больше недоступен для скачивания
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
