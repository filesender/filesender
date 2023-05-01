<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 访客证件已发送

{alternative:plain}

亲爱的先生或者女士，

已将允许访问{cfg:site_name}的证件发送到{guest.email}。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    已将允许访问<a href="{cfg:site_url}">
{cfg:site_name}</a>的证件发送到<a href="mailto:
{guest.email}">{guest.email}</a>。
</p>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>