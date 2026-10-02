<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 文件無法下載
{alternative:plain}

親愛的先生或者女士，

由於發送者（{transfer.user_email}）已從{cfg:site_name}中刪除傳輸編號{transfer.id}，因此無法下載。

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    由於發送者（<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>）已從<a href="{cfg:site_url}">{cfg:site_name}</a>中刪除傳輸編號{transfer.id}，因此無法下載。
</p>

<p>
    致以最誠摯的敬意，<br />
    {cfg:site_name}
</p>