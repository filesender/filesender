<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 訪客證件已接收
subject: {guest.subject}

{alternative:plain}

親愛的先生或者女士，

欲訪問{cfg:site_name}，請使用以下證件。
您可以使用此證件上傳一組文件，供一組人員下載。

發行人：{guest.user_email}
證件鏈接：{guest.upload_link}

{if:guest.does_not_expire}
此證件未過期。
{else}
證件將在{date:guest.expires}禁用並在之後自動刪除。
{endif}

{if:guest.message}個人信息來自{guest.user_email}:
{guest.message}{endif}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    欲訪問<a href="{cfg:site_url}">{cfg:site_name}</a>，請使用以下證件。
您可以使用此證件上傳一組文件，供一組人員下載。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">證件詳細信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>發行人</td>
            <td><a href="mailto:{guest.user_email}">
{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>證件鏈接</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a>
</td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">此邀請未過期</td>
{else}
            <td>禁用日期</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
   個人信息來自{guest.user_email}：
</p>
<p class="message">
   {guest.message}
</p>
{endif}

<p>
    致以最誠摯的敬意，<br/>
    {cfg:site_name}
</p>