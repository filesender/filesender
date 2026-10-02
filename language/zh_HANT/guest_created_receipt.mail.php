<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 訪客證件已發送

{alternative:plain}

親愛的先生或者女士，

已將允許訪問{cfg:site_name}的證件發送到{guest.email}。

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    已將允許訪問<a href="{cfg:site_url}">
{cfg:site_name}</a>的證件發送到<a href="mailto:
{guest.email}">{guest.email}</a>。
</p>

<p>
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>