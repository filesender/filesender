<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 文件无法下载
{alternative:plain}

亲爱的先生或者女士，

由于发送者（{transfer.user_email}）已从{cfg:site_name}中删除传输编号{transfer.id}，因此无法下载。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    由于发送者（<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>）已从<a href="{cfg:site_url}">{cfg:site_name}</a>中删除传输编号{transfer.id}，因此无法下载。
</p>

<p>
    致以最诚挚的敬意，<br />
    {cfg:site_name}
</p>