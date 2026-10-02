<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
مهمان شروع به آپلود کرد
{alternative:plain}

خانم / آقا

مهمان زیر آپلود فایلها را با گواهی شما شروع کرد

مهمان:‌ {guest.email}
لینک گواهی: {cfg:site_url}?s=upload&vid={guest.token}

گواهی تا تاریخ {date:guest.expires}  انقضا دارد و پس از آن به صورت خودکار منقضی می‌شود.

آرزوی بهترین‌ها برای شما
{cfg:site_name}

{alternative:html}

<p>
خانم / آقا
</p>

<p>
مهمان زیر آپلود فایلها را با گواهی شما شروع کرد
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">جزئیات گواهی</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td> مهمان </td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>لینک گواهی</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>تاریخ انقضا</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    آرزوی بهترین‌ها برای شما
<br />
    {cfg:site_name}
</p>