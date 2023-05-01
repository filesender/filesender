<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ファイルはダウンロードできなくなりました

{alternative:plain}

利用者様、

転送番号{transfer.id}は、送信者({transfer.user_email})が{cfg:site_name}から削除したため、ダウンロードできなくなりました。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    転送番号{transfer.id}は、送信者(<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>)が<a href="{cfg:site_url}">{cfg:site_name}</a>から削除したため、ダウンロードできなくなりました。
</p>

<p>
    以上、よろしくお願いいたします。<br />
    {cfg:site_name}
</p>