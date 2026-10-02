<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 關於{target.type} #{target.id}的報告

{alternative:plain}

親愛的先生或者女士，

關於{target.type}的報告如下：

{target.type}編號：{target.id}

{if:target.type == "Transfer"}
此次傳輸總大小為{size:transfer.size}， 包含{transfer.files}文件。

此次傳輸將在{date:transfer.expires}到期。

此次傳輸已發送給{transfer.recipients}個收件人。
{endif}
{if:target.type == "File"}
此文件名為{file.path}，大小為{size:file.size}以及將在
{date:file.transfer.expires}到期。
{endif}
{if:target.type == "Recipient"}
此收件人的電子郵件地址為{recipient.email}以及將在
{date:recipient.expires}到期。
{endif}

完整的傳輸日誌包括：

{raw:content.plain}

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    關於{target.type}的報告如下：<br /><br />
    
    {target.type}編號：{target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    此次傳輸總大小為{size:transfer.size}， 包含{transfer.files}文件。
<br /><br />
    
    此次傳輸將在{date:transfer.expires}到期。 <br /><br />
    
    此次傳輸已發送給{transfer.recipients}個收件人。
    {endif}
    {if:target.type == "File"}
    此文件名為{file.path}，大小為{size:file.size}以及將在
{date:file.transfer.expires}到期。
    {endif}
    {if:target.type == "Recipient"}
    此收件人的電子郵件地址為{recipient.email}以及將在
{date:recipient.expires}到期。
    {endif}
</p>

<p>
    完整的傳輸日誌包括：
    <table class="auditlog" rules="rows">
        <thead>
            <th>日期</th>
            <th>事件</th>
            <th>IP地址</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>致以最誠摯的敬意，<br/>
{cfg:site_name}</p>