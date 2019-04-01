<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
خلاصه انتقالات روزانه

{alternative:plain}

آقا/خانم:

لطفا در زیر، خلاصه‌ی دانلودها را برای انتقالات خود بیابید.  {transfer.id} (uploaded {date:transfer.created}) :

{if:events}
{each:events as event}
  - گیرنده {event.who} downloaded {if:event.what == "archive"}آرشیو{else}file {event.what_name}{endif} در {datetime:event.when}
{endeach}
{else}
No downloads
{endif}

اطلاعات بیشتر در:  {transfer.link}

آرزوی بهترین‌ها برای شما
{cfg:site_name}

{alternative:html}

<p>
آقا/خانم:
</p>

<p>
    لطفا در زیر، خلاصه‌ی دانلودها را برای انتقالات خود بیابید. {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>گیرنده {event.who} دانلود شده {if:event.what == "archive"}آرشیو{else}file {event.what_name}{endif} در {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
 دانلودی نیست
</p>
{endif}

<p>
    اطلاعات بیشتر در: <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    آرزوی بهترین‌ها برای شما<br />
    {cfg:site_name}
</p>