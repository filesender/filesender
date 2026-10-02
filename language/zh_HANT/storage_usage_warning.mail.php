<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 存儲使用警告

{alternative:plain}

親愛的先生或者女士，

{cfg:site_name}存儲使用情況警告：

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space})僅剩下{size:warning.free_space}({warning.free_space_pct}%)
{endeach}

您可以從以下URL查看額外的詳細信息：{cfg:site_url}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    {cfg:site_name}存儲使用情況警告：
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space})僅剩下{size:warning.free_space}({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    您可以從以下URL查看額外的詳細信息：<a href="{cfg:site_url}">
{cfg:site_url}</a>
</p>

<p>
    致以最誠摯的敬意，<br />
    {cfg:site_name}
</p>