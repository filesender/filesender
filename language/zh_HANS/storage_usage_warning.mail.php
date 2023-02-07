<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 存储使用警告

{alternative:plain}

亲爱的先生或者女士，

{cfg:site_name}存储使用情况警告：

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space})仅剩下{size:warning.free_space}({warning.free_space_pct}%)
{endeach}

您可以从以下URL查看额外的详细信息：{cfg:site_url}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    {cfg:site_name}存储使用情况警告：
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space})仅剩下{size:warning.free_space}({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    您可以从以下URL查看额外的详细信息：<a href="{cfg:site_url}">
{cfg:site_url}</a>
</p>

<p>
    致以最诚挚的敬意，<br />
    {cfg:site_name}
</p>