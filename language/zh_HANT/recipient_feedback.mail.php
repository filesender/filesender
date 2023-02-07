<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 反饋來自
{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}訪客{endif} {target.email}

{alternative:plain}

親愛的先生或者女士，

從
{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}訪客{endif} {target.email}收到反饋，請確認附件。

致以最誠摯的敬意，
{cfg:site_name}

{alternative:html}

<p>
    親愛的先生或者女士，
</p>

<p>
    從
{if:target_type=="recipient"}收件人{endif}
{if:target_type=="guest"}訪客{endif} {target.email}收到反饋，請確認附件。
</p>

<p>
    致以最誠摯的敬意，<br />
    {cfg:site_name}
</p>