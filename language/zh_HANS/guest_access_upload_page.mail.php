<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 访客访问上传页面

{alternative:plain}

亲爱的先生或者女士，

访客{guest.email}通过证件访问了上传页面。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    访客<a href="mailto:{guest.email}">{guest.email}</a>通过证件访问了上传页面。
</p>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>