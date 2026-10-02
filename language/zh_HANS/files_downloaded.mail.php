<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 下载通知

{alternative:plain}

亲爱的先生或者女士，

{recipient.email}从{cfg:site_name}下载了您上传的文件：

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

从以下传输页面可以访问文件以及查看详细的下载统计信息：{files.first().transfer.link}。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    {recipient.email}从{cfg:site_name}下载了您上传的文件：
</p>

<p>
   {if:files>1}
    <ul>
       {each:files as file}
            <li>{file.path} ({size:file.size})</li>
       {endeach}
    </ul>
   {else}
   {files.first().path} ({size:files.first().size})
   {endif}
</p>

<p>
    从以下传输页面可以访问文件以及查看详细的下载统计信息：
<a href="{files.first().transfer.link}">{files.first().transfer.link}</a>。
</p>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>
