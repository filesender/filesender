<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файлы удалены
subject: (Файлы удалены) {transfer.subject}

{alternative:plain}

Товарищ!

Твой трансфер с номером {transfer.id} был удален с {cfg:site_name} и больше недоступен для скачивания ({transfer.link}).

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Твой трансфер <a href="{transfer.link}"> с номером {transfer.id}</a> был удален <a href="{cfg:site_url}">{cfg:site_name}</a> и больше недоступен для скачивания.
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
