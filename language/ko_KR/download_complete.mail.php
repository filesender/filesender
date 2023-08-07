<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 다운로드 완료

{alternative:plain}

안녕하세요?

다음 파일의 다운로드가 완료되었습니다 :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    다음 파일의 다운로드가 완료되었습니다 :
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
    감사합니다,<br />
    {cfg:site_name}
</p>