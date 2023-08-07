<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 파일 배송 n°{transfer.id}에 대한 자동 알림

{alternative:plain}

안녕하세요?

{cfg:site_name} ({transfer.link})에서 아직 전송  n°{transfer.id}을 다운로드 받지 않은 수신자들에게 보내지는 자동 알림입니다 :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>에서 아직 전송 <a href="{transfer.link}">transfer n°{transfer.id}</a>을 다운로드 받지 않은 사용자들에게 보내지는 자동 알림입니다 :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>