<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 文件可下载
subject: {transfer.subject}

{alternative:plain}

亲爱的先生或者女士，

{transfer.user_email}已将以下文件上传到{cfg:site_name}，您有权下载该内容：

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

下载链接：{recipient.download_link}

此文件下载将在{date:guest.expires}禁用并在之后自动删除。

{if:transfer.message || transfer.subject}
来自{transfer.user_email}的个人消息：{transfer.subject}

{transfer.message}
{endif}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>已将以下文件上传到<a href="{cfg:site_url}">{cfg:site_name}</a>，您有权下载该内容。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">文件下载详细信息</th>
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
            <td>传输大小</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>禁用日期</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>下载链接</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    来自{transfer.user_email}的个人消息：
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    致以最诚挚的敬意，<br />
    {cfg:site_name}
</p>