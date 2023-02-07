<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 反馈来自{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}访客{endif}#{target_id}{target.email}

{alternative:plain}

亲爱的先生或者女士，

从
{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}访客{endif}#{target_id}{target.email}收到电子邮件反馈，
请确认附件。

致以最诚挚的敬意，
{cfg:site_name}

{alternative:html}

<p>
    亲爱的先生或者女士，
</p>

<p>
    从
{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}访客{endif}#{target_id}{target.email}收到电子邮件反馈，
请确认附件。
</p>

<p>
    致以最诚挚的敬意，<br/>
    {cfg:site_name}
</p>