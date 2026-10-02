<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 文件可下載
subject: {transfer.subject}

{alternative:plain}

親愛的先生或者女士，

{transfer.user_email}已將以下文件上傳到{cfg:site_name}，您有權下載該內容：

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

下載鏈接：{recipient.download_link}

此文件下載將在{date:guest.expires}禁用並在之後自動刪除。

{if:transfer.message || transfer.subject}
來自{transfer.user_email}的個人消息：{transfer.subject}

{transfer.message}
{endif}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>已將以下文件上傳到<a href="{cfg:site_url}">{cfg:site_name}</a>，您有權下載該內容。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">文件下載詳細信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>文件</td>
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
            <td>傳輸大小</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>禁用日期</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>下載鏈接</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    來自{transfer.user_email}的個人消息：
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    致以最誠摯的敬意，<br />
    {cfg:site_name}
</p>