<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:target_type=="recipient"}수신자{endif}{if:target_type=="guest"}게스트{endif}#{target_id} {target.email}가 전달한 메시지

{alternative:plain}

안녕하세요?

 {if:target_type=="recipient"}수신자{endif}{if:target_type=="guest"}게스트{endif}#{target_id} {target.email}로 부터 이메일 피드백을 받았습니다. 첨부를 참조하시기 바랍니다.

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
     {if:target_type=="recipient"}수신자{endif}{if:target_type=="guest"}게스트{endif}#{target_id} {target.email}로 부터 이메일 피드백을 받았습니다. 첨부를 참조하시기 바랍니다.
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>