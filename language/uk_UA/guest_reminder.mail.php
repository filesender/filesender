<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (нагадування) отримано ваучер гостя
subject: (нагадування) {guest.subject}

{alternative:plain}

Шановні панове,

Це нагадування. Нижче наведено ваучер, який надає доступ до {cfg:site_name}. Ви можете використовувати цей ваучер, щоб завантажити набір файлів і зробити їх доступними для завантаження групі людей.

Ким видано: {guest.user_email}
Посилання на ваучер: {guest.upload_link}

Ваучер дійсний до {date:guest.expires}, після чого він буде автоматично видалений.

{if:guest.message}Особисте повідомлення від {guest.user_email}: {guest.message}{endif}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Це нагадування. Нижче наведено ваучер, який надає доступ до <a href="{cfg:site_url}">{cfg:site_name}</a>. Ви можете використовувати цей ваучер, щоб завантажити набір файлів і зробити їх доступними для завантаження групі людей.
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
            <td>Дійсний до</td>
            <td>{date:guest.expires}</td>
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
