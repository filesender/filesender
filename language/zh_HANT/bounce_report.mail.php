<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 信息發送失敗

{alternative:plain}

親愛的先生或者女士，

其中一個或多個收件人無法接收到消息：

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - 傳輸#{bounce.target.transfer.id}收件人
{bounce.target.email}在{datetime:bounce.date}
({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - 訪客{bounce.target.email}在{datetime:bounce.date}
{endif}
{endeach}

從以下URL可以看到額外的詳細內容：{cfg:site_url}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    其中一個或多個收件人無法接收到消息：
</p>

<ul>
{each:bounces as bounce}
    <li>
   {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">傳輸#
{bounce.target.transfer.id}</a>收件人{bounce.target.email}在{datetime:bounce.date}
   {endif}{if:bounce.target_type=="Guest"}
        訪客{bounce.target.email}在{datetime:bounce.date}
   {endif}
    </li>
{endeach}
</ul>

<p>
    從以下URL可以看到額外的詳細內容：<a href="{cfg:site_url}">
{cfg:site_url}</a>
</p>

<p>
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>