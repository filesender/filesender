<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ゲストバウチャーが送信されました

{alternative:plain}

利用者様、

{cfg:site_name}へのアクセスを許可するバウチャーが{guest.email}に送信されました。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>へのアクセスを許可するバウチャーが<a href="mailto:{guest.email}">{guest.email}</a>に送信されました。
</p>

<p>
    以上、よろしくお願いいたします。<br />
    {cfg:site_name}
</p>