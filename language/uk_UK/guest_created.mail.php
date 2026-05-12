<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Отримано ваучер гостя
subject: {guest.subject}

{alternative:plain}

Шановні панове,

Нижче наведено ваучер, який надає доступ до {cfg:site_name}. Ви можете використовувати цей ваучер, щоб завантажити набір файлів і зробити їх доступними для завантаження групі людей.

Ким видано: {guest.user_email}
Посилання на ваучер: {guest.upload_link}

{if:guest.does_not_expire}
Цей ваучер не має терміну дії.
{else}
Ваучер дійсний до {date:guest.expires}, після чого він буде автоматично видалений.
{endif}

{if:guest.message}Особисте повідомлення від {guest.user_email}: {guest.message}{endif}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Нижче наведено ваучер, який надає доступ до <a href="{cfg:site_url}">{cfg:site_name}</a>. Ви можете використовувати цей ваучер, щоб завантажити набір файлів і зробити їх доступними для завантаження групі людей.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Деталі ваучера</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Ким видано</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Посилання на ваучер</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">Це запрошення не має терміну дії</td>
{else}
            <td>Дійсний до</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Особисте повідомлення від {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
