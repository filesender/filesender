<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Підтвердження завантаження

{alternative:plain}

Шановні панове,

{if:files>1}Кілька файлів{else}Файл{endif}, які ви завантажили {if:files>1}були{else}був{endif} завантажені з {cfg:site_name} за допомогою {if:files.first().transfer.get_a_link}посилання на пересилання:{else}отримувача {recipient.email}:{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Ви можете отримати доступ до своїх файлів та переглянути детальну статистику завантажень на сторінці пересилань за посиланням {files.first().transfer.link}.

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    {if:files>1}Кілька файлів{else}Файл{endif}, які ви завантажили {if:files>1}були{else}був{endif} завантажені з {cfg:site_name} за допомогою {if:files.first().transfer.get_a_link}посилання на пересилання.{else}{recipient.email}{endif}
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
    Ви можете отримати доступ до своїх файлів та переглянути детальну статистику завантажень на сторінці пересилань за посиланням <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
