<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 메시지 전달 오류

{alternative:plain}

안녕하세요?

한명 이상의 수신자가 귀하의 메시지를 수신하지 못했습니다.

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

자세한 정보는 {cfg:site_url}에서 확인하실 수 있습니다.

감사합니다.
{cfg:site_name}

{alternative:html}

<p>
  안녕하세요?
</p>

<p>
한명 이상의 수신자가 귀하의 메시지를 수신하지 못했습니다: 
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> recipient {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Guest {bounce.target.email} on {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
자세한 정보는  <a href="{cfg:site_url}">{cfg:site_url}</a>에서 확인하실 수 있습니다.    
</p>

<p>
    감사합니다.<br />
    {cfg:site_name}
</p>