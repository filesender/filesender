<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Щоденний звіт про пересилання

{alternative:plain}

Шановні панове,

Нижче наведено звіт про завантаження для вашого пересилання {transfer.id} (завантажено {date:transfer.created}) :

{if:events}
{each:events as event}
  - Отримувач {event.who} завантажив {if:event.what == "archive"}архів{else}файл {event.what_name}{endif} {datetime:event.when}
{endeach}
{else}
Завантажень немає
{endif}

Додаткову інформацію можна знайти за посиланням {transfer.link}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Нижче наведено звіт про завантаження для вашого пересилання {transfer.id} (завантажено {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Отримувач {event.who} завантажив {if:event.what == "archive"}архів{else}файл {event.what_name}{endif} {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Завантажень немає
</p>
{endif}

<p>
    Додаткову інформацію можна знайти за посиланням <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
