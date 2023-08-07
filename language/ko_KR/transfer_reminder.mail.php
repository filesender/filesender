<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (알림) 파일이 다운로드 가능함
subject: (알림) {transfer.subject}

{alternative:plain}

안녕하세요?

{transfer.user_email}가 다음 파일을 {cfg:site_name}에 업로드했고 다운로드 권한을 부여했습니다 :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

다운로드 링크: {recipient.download_link}

다운로드는 {date:transfer.expires}까지 유효하며 이후에는 자동으로 삭제됩니다.

{if:transfer.message || transfer.subject}
{transfer.user_email}의 메시지: {transfer.subject}

{transfer.message}
{endif}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>가 다음 파일을 <a href="{cfg:site_url}">{cfg:site_name}</a>에 업로드했고 다운로드 권한을 부여했습니다.
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
            <td>전송 크기</td>
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
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>