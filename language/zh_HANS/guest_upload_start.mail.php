<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 访客开始上传文件

{alternative:plain}

亲爱的先生或者女士，

以下访客已通过证件开始上传文件：

访客：{guest.email}
证件链接：{cfg:site_url}?s=upload&vid={guest.token}

证件将在{date:guest.expires}禁用并在之后自动删除。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    以下访客已通过证件开始上传文件：
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">证件详细信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>访客</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>证件链接</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">
{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>禁用日期</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>
