<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
گزارش درباره {target.type} #{target.id}

{alternative:plain}

آقا / خانم
این گزارش درباره‌ی {target.type}:

{target.type} شماره: {target.id}

{if:target.type == "Transfer"}
این انتقال {transfer.files} فایل‌هایی به اندازه کلی {size:transfer.size} دارد.

این انتقال در تاریخ {date:transfer.expires} منقضی می‌شود/ شده است.

این انتقال به {transfer.recipients} فرستاده شده است.
{endif}
{if:target.type == "File"}
این فایل {file.path}نام دارد، اندازه آن {size:file.size}است و تا تاریخ  {date:file.transfer.expires} انقضا دارد.
{endif}
{if:target.type == "Recipient"}
این گیرنده دارای ایمیل {recipient.email} است و تا تاریخ {date:recipient.expires} دسترسی دارد. 
{endif}

اینجا واقعه‌نگاری از تمام اتفاقاتی که برای انتقال رخ داده است وجود دارد:
{raw:content.plain}

آرزوی بهترین‌ها برای شما
{cfg:site_name}

{alternative:html}

<p>
آقا / خانم
</p>

<p>
    این گزارش درباره‌ی {target.type}:
<br /><br />
    
    {target.type} شماره: {target.id}<br /><br />
    
  {if:target.type == "Transfer"}
این انتقال {transfer.files} فایل‌هایی به اندازه کلی {size:transfer.size} دارد.
<br /><br />
    
    این انتقال در تاریخ {date:transfer.expires} منقضی می‌شود/ شده است.
<br /><br />
    
   این انتقال به {transfer.recipients} فرستاده شده است.
    {endif}
    {if:target.type == "File"}
این فایل {file.path}نام دارد، اندازه آن {size:file.size}است و تا تاریخ  {date:file.transfer.expires} انقضا دارد.
    {endif}
    {if:target.type == "Recipient"}
این گیرنده دارای ایمیل {recipient.email} است و تا تاریخ {date:recipient.expires} دسترسی دارد. 
    {endif}
</p>

<p>
اینجا واقعه‌نگاری از تمام اتفاقاتی که برای انتقال رخ داده است وجود دارد:
    <table class="auditlog" rules="rows">
        <thead>
            <th>تاریخ</th>
            <th>رویداد</th>
            <th>IP address</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>آرزوی بهترین‌ها برای شما
<br/>
{cfg:site_name}</p>