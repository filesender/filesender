<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 下載完成

{alternative:plain}

親愛的先生或者女士，

以下文件已下載完成：

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    以下文件已下載完成：
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
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>