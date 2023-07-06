<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 일일 전송 요약

{alternative:plain}

안녕하세요?

귀하의 전송 {transfer.id}에 대한 다운로드를 요약하면 아래와 같습니다({date:transfer.created}에 업로드 됨) :

{if:events}
{each:events as event}
  - 수신자 {event.who}가 {if:event.what == "archive"}압축파일{else}파일 {event.what_name}{endif}를 {datetime:event.when}에 다운로드 했습니다
{endeach}
{else}
다운로드 내역이 없음
{endif}

더 자세한 내용은 {transfer.link}를 참조하십시요

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
   안녕하세요?
</p>

<p>
    귀하의 전송 {transfer.id}에 대한 다운로드를 요약하면 아래와 같습니다({date:transfer.created}에 업로드 됨) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>수신자 {event.who}가 {if:event.what == "archive"}압축파일{else}파일 {event.what_name}{endif}를 {datetime:event.when}에 다운로드 했습니다</li>
{endeach}
</ul>
{else}
<p>
    다운로드 내역이 없음
</p>
{endif}

<p>
   더 자세한 내용은 <a href="{transfer.link}">{transfer.link}</a>을 참조하십시요
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>
