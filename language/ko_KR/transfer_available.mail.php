<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 파일이 다운로드 가능함
subject: {transfer.subject}

{alternative:plain}

안녕하세요?

다음 파일이 {transfer.user_email}에 의해  {cfg:site_name}에 업로드되었고 다운로드 받을 수 있는 권한이 부여되었습니다 :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

다운로드 링크: {recipient.download_link}

다운로드는 {date:transfer.expires}까지 유효하며 이후에는 자동으로 삭제됩니다.

{if:transfer.message || transfer.subject}
Personal message from {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    다음 파일이 <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>에 의해 <a href="{cfg:site_url}">{cfg:site_name}</a>에 업로드되었고 다운로드 받을 수 있는 권한이 부여되었습니다.
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
        {if:transfer.files>1}
        <tr>
            <td>파일 크기</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>만료일</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>다운로드 링크</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    {transfer.user_email}의 메시지:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>