<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ゲストがアップロードページにアクセスしました

{alternative:plain}

利用者様、

ゲスト{guest.email}がバウチャーからアップロードページにアクセスしました。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    ゲスト<a href="mailto:{guest.email}">{guest.email}</a>がバウチャーからアップロードページにアクセスしました。
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>