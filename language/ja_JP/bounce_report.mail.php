<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: メッセージ送信の失敗

{alternative:plain}

利用者様、

受信者のうち1人以上がメッセージの受信に失敗しました:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - 転送#{bounce.target.transfer.id}受信者{bounce.target.email}、日時{datetime:bounce.date}({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - ゲスト{bounce.target.email}、日時{datetime:bounce.date}
{endif}
{endeach}

次のURLから追加の詳細をご覧になれます：{cfg:site_url}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    受信者のうち1人以上がメッセージの受信に失敗しました:
</p>

<ul>
{each:bounces as bounce}
    <li>
   {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">転送#{bounce.target.transfer.id}</a>受信者{bounce.target.email}、日時{datetime:bounce.date}
   {endif}{if:bounce.target_type=="Guest"}
        ゲスト{bounce.target.email}、日時{datetime:bounce.date}
   {endif}
    </li>
{endeach}
</ul>

<p>
    次のURLから追加の詳細をご覧になれます：<a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>