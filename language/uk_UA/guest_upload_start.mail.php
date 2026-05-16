<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Гість розпочав завантаження файлів

{alternative:plain}

Шановні панове,

Наступний гість розпочав завантаження файлів за допомогою вашого ваучера :

Гість: {guest.email}
Посилання на ваучер: {cfg:site_url}?s=upload&vid={guest.token}

Ваучер дійсний до {date:guest.expires}, після чого він буде автоматично видалений.

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Наступний гість розпочав завантаження файлів за допомогою вашого ваучера :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Деталі ваучера</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Гість</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Посилання на ваучер</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Дійсний до</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
