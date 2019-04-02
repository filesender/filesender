<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
گواهی مهمان دریافت شد

{alternative:plain}

آقا / خانم

لطفا گواهی دسترسی به {cfg:site_name} را پیدا کنید. شما با این گواهی می‌توانید مجموعه‌ای از فایل‌ها را بارگذاری کنید و امکان ارسال آن ها را به گروهی دیگر فراهم کنید.
صادرکننده: {guest.user_email}
لینک گواهی:  {guest.upload_link}

این گواهی تا تاریخ {date:guest.expires} انقضا دارد و پس از آن به صورت خودکار باطل می‌شود.
{if:guest.message}پیام شخصی از {guest.user_email}: {guest.message}{endif}

آرزوی بهترین‌ها برای شما
{cfg:site_name}

{alternative:html}

<p>
آقا / خانم
</p>

<p>
لطفا گواهی دسترسی به <a href="{cfg:site_url}">{cfg:site_name}</a> را پیدا کنید. شما با این گواهی می‌توانید مجموعه‌ای از فایل‌ها را بارگذاری کنید و امکان ارسال آن ها را به گروهی دیگر فراهم کنید.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>صادرکننده</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>لینک گواهی</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>انقضا</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    پیام شخصی از {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    آرزوی بهترین‌ها برای شما
<br />
    {cfg:site_name}
</p>