<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {target.type} #{target.id}에 대한 보고

{alternative:plain}

안녕하세요?

{target.type}에 대한 보고를 전달해 드립니다 :

{target.type} 번호 : {target.id}

{if:target.type == "Transfer"}
이 전송에는 전체 크기가 {size:transfer.size}인 {transfer.files} 파일이 있습니다.

이 전송은 {date:transfer.expires}까지 유효합니다.

이 전송이 {transfer.recipients}에게 보내졌습니다.
{endif}
{if:target.type == "File"}
파일명은 {file.path}이고 파일크기는 {size:file.size}이며 {date:file.transfer.expires}까지 유효합니다.
{endif}
{if:target.type == "Recipient"}
수신자의 이메일 주소는 {recipient.email} 이고 {date:recipient.expires}까지 유효합니다.
{endif}

전송에서 발생한 전체 로그는 다음과 같습니다 :

{raw:content.plain}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    {target.type}에 대한 보고를 전달해 드립니다 : <br /><br />
    
    {target.type} 번호 : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    이 전송에는 전체 크기가 {size:transfer.size}인 {transfer.files} 파일이 있습니다.<br /><br />
    
    이 전송은 {date:transfer.expires}까지 유효합니다.<br /><br />
    
    이 전송이 {transfer.recipients}에게 보내졌습니다.
    {endif}
    {if:target.type == "File"}
    파일명은 {file.path}이고 파일크기는 {size:file.size}이며 {date:file.transfer.expires}까지 유효합니다.
    {endif}
    {if:target.type == "Recipient"}
    수신자의 이메일 주소는 {recipient.email} 이고 {date:recipient.expires}까지 유효합니다.
    {endif}
</p>

<p>
    전송에서 발생한 전체 로그는 다음과 같습니다 :
    <table class="auditlog" rules="rows">
        <thead>
            <th>날짜</th>
            <th>이벤트</th>
            <th>IP 주소</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>감사합니다,<br/>
{cfg:site_name}</p>