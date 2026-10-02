<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 업로드 페이지에 게스트 접근

{alternative:plain}

안녕하세요?

게스트 {guest.email}가 귀하의 바우쳐를 사용해 업로드 페이지에 접근했습니다.

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
  안녕하세요?
</p>

<p>
    게스트 <a href="mailto:{guest.email}">{guest.email}</a>가 귀하의 바우쳐를 사용해 업로드 페이지에 접근했습니다.
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>