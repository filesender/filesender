<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 針對文件傳輸編號{transfer.id}發送的自動提醒

{alternative:plain}

親愛的先生或者女士，

已向還未從{cfg:site_name} ({transfer.link})中文件傳輸編號{transfer.id}下載文件的收件人發送自動提醒：

{each:recipients as recipient}
  - {recipient.email}
{endeach}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    已向還未從<a href="{cfg:site_url}">{cfg:site_name}</a>中文件<a href="{transfer.link}">傳輸編號{transfer.id}</a>下載文件的收件人發送自動提醒：
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    致以最誠摯的敬意，<br />
    {cfg:site_name}
</p>