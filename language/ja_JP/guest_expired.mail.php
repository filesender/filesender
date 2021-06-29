<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ゲストバウチャーの有効期限切れ

{alternative:plain}

利用者様、

{guest.user_email}からのゲストバウチャーの有効期限が切れています。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
   <a href="mailto:{guest.user_email}">{guest.user_email}</a>からのゲストバウチャーの有効期限が切れています。
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>