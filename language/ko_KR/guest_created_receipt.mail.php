<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 게스트 바우쳐가 보내짐

{alternative:plain}

안녕하세요?

{cfg:site_name}에 접근을 허용하는 바우쳐가 {guest.email}에게 보내졌습니다.

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>에 접근을 허용하는 바우쳐가 <a href="mailto:{guest.email}">{guest.email}</a>에게 보내졌습니다.
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>
