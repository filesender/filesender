<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 파일을 더 이상 다운로드할 수 없음

{alternative:plain}

안녕하세요?

{cfg:site_name}에서 n°{transfer.id}이 송신자({transfer.user_email})에 의해 삭제되어 더 이상 다운로드를 할 수 없습니다.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>에서 n°{transfer.id}이 송신자(<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>)에 의해 삭제되어 더 이상 다운로드를 할 수 없습니다.
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>