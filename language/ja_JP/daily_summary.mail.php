<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 転送の日次サマリー

{alternative:plain}

利用者様、

あなたの転送{transfer.id}のダウンロードのサマリーは、次のとおりです({date:transfer.created}にアップロード):

{if:events}
{each:events as event}
  - 受信者{event.who}が{if:event.what == "archive"}アーカイブ{else}ファイル{event.what_name}{endif}を{datetime:event.when}にダウンロードしました。
{endeach}
{else}
ダウンロードなし
{endif}

次のURLから追加の詳細をご覧になれます:{transfer.link}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    あなたの転送{transfer.id}のダウンロードのサマリーは、次のとおりです({date:transfer.created}にアップロード):
 </p>

{if:events}
<ul>
{each:events as event}
    <li>受信者{event.who}が{if:event.what == "archive"}アーカイブ{else}ファイル{event.what_name}{endif}を{datetime:event.when}にダウンロードしました。</li>
{endeach}
</ul>
{else}
<p>
    ダウンロードなし
</p>
{endif}

<p>
    次のURLから追加の詳細をご覧になれます：<a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>