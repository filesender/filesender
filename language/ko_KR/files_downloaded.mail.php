<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 다운로드 됨

{alternative:plain}

안녕하세요?

업로드한 파일을 {cfg:site_name}에서 {recipient.email}가 다운로드했습니다:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

{files.first().transfer.link}의 전송 페이지에서 다운로드 현황을 자세히 확인할 수 있습니다.

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    {업로드한 파일을 {cfg:site_name}에서 {recipient.email}가 다운로드했습니다.
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
   <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>의 전송 페이지에서 다운로드 현황을 자세히 확인할 수 있습니다..
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>