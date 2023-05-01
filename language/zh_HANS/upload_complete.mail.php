<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 文件已成功上传

{alternative:plain}

亲爱的先生或者女士，

以下文件已成功上传到{cfg:site_name}。

可使用以下链接下载文件：{transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

更多信息：{transfer.link}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    以下文件已成功上传到<a href="{cfg:site_url}">{cfg:site_name}</a>。
</p>

<p>
可使用以下链接下载文件：<a href="
{transfer.download_link}">{transfer.download_link}</a>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">文件上传详细信息</th>
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
        <tr>
            <td>文件大小</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>更多信息</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    致以最诚挚的敬意，<br />
    {cfg:site_name}
</p>