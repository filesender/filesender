<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (Напоминание) Получен гостевой ваучер
subject: (Напоминание) {guest.subject}

{alternative:plain}

Товарищ!

Мы напоминаем, что у тебя всё ещё есть гостевой ваучер, дающий доступ к {cfg:site_name}.
Ты можешь использовать этот ваучер для единовременной загрузки на сервер файлов для того, что бы их могли скачать другие пользователи.

Издатель: {guest.user_email}
Ссылка на ваучер: {guest.upload_link}
Срок истечения ваучера: {date:guest.expires}. После срока истечения он будет автоматически удален.

{if:guest.message}Персональное сообщение от {guest.user_email}: {guest.message}{endif}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Мы напоминаем, что у тебя всё ещё есть гостевой ваучер, дающий доступ к <a href="{cfg:site_url}">{cfg:site_name}</a>.<br/>
    Ты можешь использовать этот ваучер для единовременной загрузки на сервер файлов для того, что бы их могли скачать другие пользователи.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Детали ваучера</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Издатель</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Ссылка на ваучер</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Срок истечения ваучера</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Персональное сообщение от {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
