<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
تحویل پیام انجام نشد.

{alternative:plain}

آقا/خانم 

یک یا چند نفر از گیرندگان شما پیام به دستشان نرسیده

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

اطلاعات بیشتر در:  {cfg:site_url}

آرزوی بهترین‌ها برای شما
{cfg:site_name}

{alternative:html}

<p>
آقا/خانم 

</p>

<p>
یک یا چند نفر از گیرندگان شما پیام به دستشان نرسیده
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> recipient {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Guest {bounce.target.email} on {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    اطلاعات بیشتر در: <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
