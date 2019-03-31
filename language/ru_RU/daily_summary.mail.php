<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Дневная статистика

{alternative:plain}

Товарищ!

Ниже показана статистика скачиваний твоих трансферов {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - Получатель {event.who} скачал {if:event.what == "archive"}архив{else}файл {event.what_name}{endif}  {datetime:event.when}
{endeach}
{else}
Нет скачиваний
{endif}

Подробности можно найти здесь: {transfer.link}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
  Ниже показана статистика скачиваний твоих трансферов {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Получатель {event.who} скачал {if:event.what == "archive"}архив{else}файл {event.what_name}{endif}  {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
  Нет скачиваний
</p>
{endif}

<p>
  Подробности можно найти здесь: <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    С наилучшими пожеланиями,,<br />
    {cfg:site_name}
</p>
