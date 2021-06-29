<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:target_type=="recipient"}受信者{endif}{if:target_type=="guest"}ゲスト{endif}{target.email}からのフィードバック

{alternative:plain}

利用者様、

{if:target_type=="recipient"}受信者{endif}{if:target_type=="guest"}ゲスト{endif}{target.email}からメールによるフィードバックを受信しました。添付をご確認ください。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
  {if:target_type=="recipient"}受信者{endif}{if:target_type=="guest"}ゲスト{endif}{target.email}からメールによるフィードバックを受信しました。添付をご確認ください。
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>