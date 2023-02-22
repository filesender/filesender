<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 针对文件传输编号{transfer.id}发送的自动提醒

{alternative:plain}

亲爱的先生或者女士，

已向还未从{cfg:site_name} ({transfer.link})中文件传输编号{transfer.id}下载文件的收件人发送自动提醒：

{each:recipients as recipient}
  - {recipient.email}
{endeach}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    已向还未从<a href="{cfg:site_url}">{cfg:site_name}</a>中文件<a href="{transfer.link}">传输编号{transfer.id}</a>下载文件的收件人发送自动提醒：
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    致以最诚挚的敬意，<br />
    {cfg:site_name}
</p>