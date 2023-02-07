<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 訪客開始上傳文件

{alternative:plain}

親愛的先生或者女士，

以下訪客已通過證件開始上傳文件：

訪客：{guest.email}
證件鏈接：{cfg:site_url}?s=upload&vid={guest.token}

證件將在{date:guest.expires}禁用並在之後自動刪除。

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    以下訪客已通過證件開始上傳文件：
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">證件詳細信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>訪客</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>證件鏈接</td>
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
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>