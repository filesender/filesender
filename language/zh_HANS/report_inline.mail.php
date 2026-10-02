<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 关于{target.type} #{target.id}的报告

{alternative:plain}

亲爱的先生或者女士，

关于{target.type}的报告如下：

{target.type}编号：{target.id}

{if:target.type == "Transfer"}
此次传输总大小为{size:transfer.size}， 包含{transfer.files}文件。

此次传输将在{date:transfer.expires}到期。

此次传输已发送给{transfer.recipients}个收件人。
{endif}
{if:target.type == "File"}
此文件名为{file.path}，大小为{size:file.size}以及将在
{date:file.transfer.expires}到期。
{endif}
{if:target.type == "Recipient"}
此收件人的电子邮件地址为{recipient.email}以及将在
{date:recipient.expires}到期。
{endif}

完整的传输日志包括：

{raw:content.plain}

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    关于{target.type}的报告如下：<br /><br />
    
    {target.type}编号：{target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    此次传输总大小为{size:transfer.size}， 包含{transfer.files}文件。
<br /><br />
    
    此次传输将在{date:transfer.expires}到期。<br /><br />
    
    此次传输已发送给{transfer.recipients}个收件人。
    {endif}
    {if:target.type == "File"}
    此文件名为{file.path}，大小为{size:file.size}以及将在
{date:file.transfer.expires}到期。
    {endif}
    {if:target.type == "Recipient"}
    此收件人的电子邮件地址为{recipient.email}以及将在
{date:recipient.expires}到期。
    {endif}
</p>

<p>
    完整的传输日志包括：
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

<p>致以最诚挚的敬意，<br/>
{cfg:site_name}</p>