<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файлы скачаны

{alternative:plain}

Товарищ!

{if:files>1}Файлы{else}Файл{endif}, которые ты загрузил на сервер, {if:files>1}были скачаны{else}был скачан{endif} с {cfg:site_name} пользователем {recipient.email}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Статистику скачиваний можно посмотреть здесь: {files.first().transfer.link}.

С наилучшими полжеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    {if:files>1}Файлы{else}Файл{endif}, которые ты загрузил на сервер, {if:files>1}были скачаны{else}был скачан{endif} с {cfg:site_name} пользователем {recipient.email}
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Статистику скачиваний можно посмотреть здесь: <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
