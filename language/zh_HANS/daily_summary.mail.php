<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 传输日期摘要

{alternative:plain}

亲爱的先生或者女士，

请在下面找到传输的下载摘要
{transfer.id}(已上传{date:transfer.created})：

{if:events}
{each:events as event}
  - 收件人{event.who}已将{if:event.what == 
"archive"}存档{else}文件{event.what_name}{endif}下载到
{datetime:event.when}
{endeach}
{else}
无下载
{endif}

从以下URL可以看到额外的详细内容：{transfer.link}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    请在下面找到传输的下载摘要
{transfer.id}(已上传{date:transfer.created})：
 </p>

{if:events}
<ul>
{each:events as event}
   <li>收件人{event.who}已将{if:event.what == 
"archive"}存档{else}文件{event.what_name}{endif}下载到
{datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    无下载
</p>
{endif}

<p>
    从以下URL可以看到额外的详细内容：<a href="{transfer.link}">
{transfer.link}</a>
</p>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>