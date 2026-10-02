<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject：（提醒）访客证件已接收
subject：（提醒）{guest.subject}

{alternative:plain}

亲爱的先生或者女士，

提醒，欲访问{cfg:site_name}，请使用以下证件。
您可以使用此证件上传一组文件，供一组人员下载。

发行人：{guest.user_email}
访客链接：{guest.upload_link}

证件将在{date:guest.expires}禁用并在之后自动删除。

{if:guest.message}个人信息来自{guest.user_email}：
{guest.message}{endif}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    提醒，欲访问<a href="{cfg:site_url}">{cfg:site_name}</a>，请使用以下证件。您可以使用此证件上传一组文件，供一组人员下载。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">证件详细信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>发行人</td>
            <td><a href="mailto:{guest.user_email}">
{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>证件链接</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a>
</td>
        </tr>
        <tr>
            <td>禁用日期</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
   个人信息来自{guest.user_email}：
</p>
<p class="message">
   {guest.message}
</p>
{endif}

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>
