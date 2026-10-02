<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
بازخورد از طرف
{if:target_type=="recipient"}
گیرنده
{endif}{if:target_type=="guest"}
مهمان
{endif} {target.email}

{alternative:plain}

آقا/ خانم

ما یک ایمیل بازخورد از طرف 
{if:target_type=="recipient"}
گیرنده
{endif}{if:target_type=="guest"}
نویسنده
{endif} {target.email},
شما دریافت کرده ایم.
لطفا آن را بیابید
Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    We received an email feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, please find it enclosed.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
