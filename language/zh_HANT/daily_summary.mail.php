<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 傳輸日期摘要

{alternative:plain}

親愛的先生或者女士，

請在下面找到傳輸的下載摘要
{transfer.id}(已上傳{date:transfer.created})：

{if:events}
{each:events as event}
  - 收件人{event.who}已將{if:event.what == 
"archive"}存檔{else}文件{event.what_name}{endif}下載到
{datetime:event.when}
{endeach}
{else}
無下載
{endif}

從以下URL可以看到額外的詳細內容：{transfer.link}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    請在下面找到傳輸的下載摘要
{transfer.id}(已上傳{date:transfer.created})：
 </p>

{if:events}
<ul>
{each:events as event}
   <li>收件人{event.who}已將{if:event.what == 
"archive"}存檔{else}文件{event.what_name}{endif}下載到
{datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    無下載
</p>
{endif}

<p>
    從以下URL可以看到額外的詳細內容：<a href="{transfer.link}">
{transfer.link}</a>
</p>

<p>
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>