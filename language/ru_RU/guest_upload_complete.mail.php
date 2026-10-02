<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Гость завершил загрузку файлов на сервер

{alternative:plain}

Товарищ!

Гость завершил загрузку файлов на сервер.
Вот подробности:

Гость: {guest.email}
Ссылка на ваучер: {cfg:site_url}?s=upload&vid={guest.token}

Ваучер истекает {date:guest.expires} и после этой даты будет автоматически удален.

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Гость завершил загрузку файлов на сервер.<br/>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Подробности</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Гость</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Ссылка на ваучер</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Истекает</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
