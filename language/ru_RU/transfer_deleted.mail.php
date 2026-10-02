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

Трансфер номер {transfer.id} был удален с {cfg:site_name} отправителем ({transfer.user_email}) и больше недоступен для скачивания.

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
  Товарищ!
</p>

<p>
    Трансфер номер {transfer.id} был удален с <a href="{cfg:site_url}">{cfg:site_name}</a> отправителем (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) и больше недоступен для скачивания..
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
