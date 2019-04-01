<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Автоматическое напоминание о трансфере номер {transfer.id}
subject: (Автоматическое напоминание о трансфере) {transfer.subject}

{alternative:plain}

Товрищ!

Автоматическое напоминание отправлено получателям, которые еще не скачали файлы из твоего трансфера номер {transfer.id} на {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Автоматическое напоминание отправлено получателям, которые еще не скачали файлы из твоего трансфера <a href="{transfer.link}"> номер {transfer.id}</a> на <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
