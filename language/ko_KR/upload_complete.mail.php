<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 파일이 성공적으로 업로드 됨

{alternative:plain}

안녕하세요?

다음 파일이 {cfg:site_name}에 성공적으로 업로드 되었습니다.

다음 링크를 통해 다운로드 받을 수 있습니다: 
{transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

추가 정보: {transfer.link}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    다음 파일이 <a href="{cfg:site_url}">{cfg:site_name}</a>에 성공적으로 업로드 되었습니다.
</p>

<p>
<a href="{transfer.download_link}">{transfer.download_link}</a>를 통해 다운로드 받을 수 있습니다
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        <tr>
            <td>Size</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>More information</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>