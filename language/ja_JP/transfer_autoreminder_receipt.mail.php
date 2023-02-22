<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ファイル転送番号{transfer.id}に関して送信された自動リマインダー

{alternative:plain}

利用者様、

{cfg:site_name}上の転送番号{transfer.id}からファイルをダウンロードしなかった受信者に自動リマインダーが送信されました({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>上の<a href="{transfer.link}">転送番号{transfer.id}</ a>からファイルをダウンロードしなかった受信者に自動リマインダーが送信されました:
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    以上、よろしくお願いいたします。<br />
    {cfg:site_name}
</p>